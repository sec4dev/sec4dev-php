<?php

declare(strict_types=1);

namespace Sec4Dev\Exception;

class RateLimitException extends Sec4DevException
{
    public int $retryAfter = 0;
    public int $limit = 0;
    public int $remaining = 0;

    public function __construct(
        string $message,
        int $statusCode = 429,
        $responseBody = null,
        int $retryAfter = 0,
        int $limit = 0,
        int $remaining = 0
    ) {
        parent::__construct($message, $statusCode, $responseBody);
        $this->retryAfter = $retryAfter;
        $this->limit = $limit;
        $this->remaining = $remaining;
    }
}
