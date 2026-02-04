<?php

declare(strict_types=1);

namespace Sec4Dev\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Sec4Dev\Exception\AuthenticationException;
use Sec4Dev\Exception\ForbiddenException;
use Sec4Dev\Exception\NotFoundException;
use Sec4Dev\Exception\PaymentRequiredException;
use Sec4Dev\Exception\RateLimitException;
use Sec4Dev\Exception\Sec4DevException;
use Sec4Dev\Exception\ServerException;
use Sec4Dev\Exception\ValidationException;
use Sec4Dev\Model\RateLimitInfo;

final class HttpClient
{
    private const SDK_VERSION = '1.0.0';
    private const CONNECT_TIMEOUT = 10.0;
    private const READ_TIMEOUT = 30.0;

    private string $baseUrl;
    private string $apiKey;
    private int $timeoutMs;
    private int $retries;
    private int $retryDelayMs;
    private ?\Closure $onRateLimit;
    private Client $guzzle;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        int $timeoutMs = 30000,
        int $retries = 3,
        int $retryDelayMs = 1000,
        ?\Closure $onRateLimit = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeoutMs = $timeoutMs;
        $this->retries = $retries;
        $this->retryDelayMs = $retryDelayMs;
        $this->onRateLimit = $onRateLimit;
        $readTimeout = $timeoutMs >= 1000 ? $timeoutMs / 1000.0 : self::READ_TIMEOUT;
        $this->guzzle = new Client([
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'timeout' => $readTimeout,
            'headers' => [
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'sec4dev-php/' . self::SDK_VERSION,
            ],
        ]);
    }

    /**
     * @return array{0: array, 1: RateLimitInfo}
     * @throws Sec4DevException
     */
    public function post(string $path, array $body): array
    {
        $url = $this->baseUrl . $path;
        $lastException = null;
        $lastStatus = null;
        $lastBody = null;
        $lastResponse = null;

        for ($attempt = 0; $attempt <= $this->retries; $attempt++) {
            try {
                $response = $this->guzzle->post($url, [
                    'json' => $body,
                ]);
                $rateLimit = $this->parseRateLimit($response);
                if ($this->onRateLimit !== null) {
                    ($this->onRateLimit)($rateLimit);
                }
                $decoded = json_decode((string) $response->getBody(), true);
                return [$decoded ?? [], $rateLimit];
            } catch (RequestException $e) {
                $response = $e->getResponse();
                $statusCode = $response !== null ? $response->getStatusCode() : 0;
                $responseBody = null;
                if ($response !== null) {
                    $raw = (string) $response->getBody();
                    $responseBody = json_decode($raw, true) ?? $raw;
                }

                $rateLimit = $response !== null ? $this->parseRateLimit($response) : new RateLimitInfo(0, 0, 0);
                if ($this->onRateLimit !== null) {
                    ($this->onRateLimit)($rateLimit);
                }

                if ($statusCode === 429) {
                    $retryAfter = (int) ($response->getHeaderLine('Retry-After') ?: 60);
                    if ($attempt < $this->retries) {
                        usleep($retryAfter * 1_000_000);
                        continue;
                    }
                    throw $this->exceptionFromResponse(429, $responseBody, $response, $retryAfter, $rateLimit);
                }

                $ex = $this->exceptionFromResponse($statusCode, $responseBody, $response);
                if (!$this->isRetryable($statusCode, false)) {
                    throw $ex;
                }
                $lastException = $ex;
                $lastStatus = $statusCode;
                $lastBody = $responseBody;
                $lastResponse = $response;
            } catch (GuzzleException $e) {
                $lastException = new Sec4DevException($e->getMessage(), 0, null);
                if ($attempt < $this->retries && $this->isRetryable(null, true)) {
                    $this->sleep($attempt);
                    continue;
                }
                throw $lastException;
            }
        }

        if ($lastResponse !== null && $lastStatus !== null) {
            throw $this->exceptionFromResponse($lastStatus, $lastBody, $lastResponse);
        }
        throw $lastException ?? new Sec4DevException('Request failed after retries', 0, null);
    }

    private function parseRateLimit(ResponseInterface $response): RateLimitInfo
    {
        $getInt = fn (string $name): int => (int) $response->getHeaderLine($name);
        return new RateLimitInfo(
            $getInt('X-RateLimit-Limit'),
            $getInt('X-RateLimit-Remaining'),
            $getInt('X-RateLimit-Reset')
        );
    }

    private function isRetryable(?int $statusCode, bool $networkError): bool
    {
        if ($networkError) {
            return true;
        }
        if ($statusCode === null) {
            return true;
        }
        return $statusCode === 429 || in_array($statusCode, [500, 502, 503, 504], true);
    }

    private function sleep(int $attempt): void
    {
        $delayMs = $this->retryDelayMs * (2 ** $attempt) + random_int(0, 100);
        usleep($delayMs * 1000);
    }

    /**
     * @param mixed $body
     * @param ResponseInterface|null $response
     */
    private function exceptionFromResponse(
        int $statusCode,
        $body,
        ?ResponseInterface $response = null,
        int $retryAfter = 0,
        ?RateLimitInfo $rateLimit = null
    ): Sec4DevException {
        $message = 'Unknown error';
        if (is_array($body) && isset($body['detail'])) {
            $detail = $body['detail'];
            $message = is_string($detail) ? $detail : (string) json_encode($detail);
        }

        $limit = $rateLimit?->limit ?? 0;
        $remaining = $rateLimit?->remaining ?? 0;
        if ($response !== null && $rateLimit === null) {
            $rateLimit = $this->parseRateLimit($response);
            $limit = $rateLimit->limit;
            $remaining = $rateLimit->remaining;
            if ($retryAfter === 0) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
            }
        }

        return match ($statusCode) {
            401 => new AuthenticationException($message, 401, $body),
            402 => new PaymentRequiredException($message, 402, $body),
            403 => new ForbiddenException($message, 403, $body),
            404 => new NotFoundException($message, 404, $body),
            422 => new ValidationException($message, 422, $body),
            429 => new RateLimitException($message, 429, $body, $retryAfter, $limit, $remaining),
            default => $statusCode >= 500 ? new ServerException($message, $statusCode, $body) : new Sec4DevException($message, $statusCode, $body),
        };
    }
}
