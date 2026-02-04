<?php

declare(strict_types=1);

namespace Sec4Dev;

use Sec4Dev\Exception\ValidationException;
use Sec4Dev\Http\HttpClient;
use Sec4Dev\Model\RateLimitInfo;

final class Client
{
    public const DEFAULT_BASE_URL = 'https://api.sec4.dev/api/v1';

    private HttpClient $http;
    private RateLimitInfo $rateLimit;
    private EmailService $emailService;
    private IPService $ipService;

    public function __construct(
        string $apiKey,
        ?string $baseUrl = null,
        int $timeout = 30000,
        int $retries = 3,
        int $retryDelay = 1000,
        ?\Closure $onRateLimit = null
    ) {
        $key = $apiKey !== null ? trim($apiKey) : '';
        if ($key === '' || !str_starts_with($key, 'sec4_')) {
            throw new ValidationException('API key must start with sec4_', 422);
        }
        $baseUrl = $baseUrl !== null && $baseUrl !== '' ? rtrim(trim($baseUrl), '/') : self::DEFAULT_BASE_URL;
        $this->rateLimit = new RateLimitInfo(0, 0, 0);

        $captureRateLimit = function (RateLimitInfo $info) use ($onRateLimit): void {
            $this->rateLimit = $info;
            if ($onRateLimit !== null) {
                $onRateLimit($info);
            }
        };

        $this->http = new HttpClient(
            $baseUrl,
            $key,
            $timeout,
            $retries,
            $retryDelay,
            $captureRateLimit
        );
        $this->emailService = new EmailService($this->http);
        $this->ipService = new IPService($this->http);
    }

    public function email(): EmailService
    {
        return $this->emailService;
    }

    public function ip(): IPService
    {
        return $this->ipService;
    }

    public function getRateLimit(): RateLimitInfo
    {
        return $this->rateLimit;
    }
}
