<?php

declare(strict_types=1);

namespace Sec4Dev;

use Sec4Dev\Http\HttpClient;
use Sec4Dev\Model\IPCheckResult;

final class IPService
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    public function check(string $ip): IPCheckResult
    {
        Validation::validateIp($ip);
        [$data] = $this->http->post('/ip/check', ['ip' => trim($ip)]);
        return IPCheckResult::fromArray($data);
    }

    public function isHosting(string $ip): bool
    {
        return $this->check($ip)->signals->isHosting;
    }

    public function isVpn(string $ip): bool
    {
        return $this->check($ip)->signals->isVpn;
    }

    public function isTor(string $ip): bool
    {
        return $this->check($ip)->signals->isTor;
    }

    public function isResidential(string $ip): bool
    {
        return $this->check($ip)->signals->isResidential;
    }

    public function isMobile(string $ip): bool
    {
        return $this->check($ip)->signals->isMobile;
    }
}
