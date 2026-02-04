<?php

declare(strict_types=1);

namespace Sec4Dev\Model;

final class RateLimitInfo
{
    public int $limit;
    public int $remaining;
    public int $resetSeconds;

    public function __construct(int $limit = 0, int $remaining = 0, int $resetSeconds = 0)
    {
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->resetSeconds = $resetSeconds;
    }
}
