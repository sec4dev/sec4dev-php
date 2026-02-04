<?php

declare(strict_types=1);

namespace Sec4Dev\Tests;

use PHPUnit\Framework\TestCase;
use Sec4Dev\Model\EmailCheckResult;
use Sec4Dev\Model\IPCheckResult;

final class ModelTest extends TestCase
{
    public function testEmailCheckResultFromArray(): void
    {
        $data = [
            'email' => 'user@tempmail.com',
            'domain' => 'tempmail.com',
            'is_disposable' => true,
        ];
        $result = EmailCheckResult::fromArray($data);
        $this->assertSame('user@tempmail.com', $result->email);
        $this->assertSame('tempmail.com', $result->domain);
        $this->assertTrue($result->isDisposable);
    }

    public function testIPCheckResultFromArray(): void
    {
        $data = [
            'ip' => '203.0.113.42',
            'classification' => 'hosting',
            'confidence' => 0.95,
            'signals' => [
                'is_hosting' => true,
                'is_residential' => false,
                'is_vpn' => false,
            ],
            'network' => ['asn' => 16509, 'org' => 'Amazon', 'provider' => 'AWS'],
            'geo' => ['country' => 'US', 'region' => null],
        ];
        $result = IPCheckResult::fromArray($data);
        $this->assertSame('203.0.113.42', $result->ip);
        $this->assertSame('hosting', $result->classification);
        $this->assertEqualsWithDelta(0.95, $result->confidence, 0.001);
        $this->assertTrue($result->signals->isHosting);
        $this->assertFalse($result->signals->isVpn);
        $this->assertSame('AWS', $result->network->provider);
        $this->assertSame('US', $result->geo->country);
    }
}
