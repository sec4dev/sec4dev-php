<?php

declare(strict_types=1);

namespace Sec4Dev\Tests;

use PHPUnit\Framework\TestCase;
use Sec4Dev\Client;
use Sec4Dev\Exception\ValidationException;

final class ClientTest extends TestCase
{
    public function testAcceptsValidApiKey(): void
    {
        $client = new Client('sec4_test_key_123');
        $this->assertNotNull($client->email());
        $this->assertNotNull($client->ip());
        $rl = $client->getRateLimit();
        $this->assertSame(0, $rl->limit);
        $this->assertSame(0, $rl->remaining);
    }

    public function testRejectsEmptyApiKey(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('sec4_');
        new Client('');
    }

    public function testRejectsApiKeyWithoutPrefix(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('sec4_');
        new Client('invalid_key');
    }

    public function testRejectsWhitespaceOnlyApiKey(): void
    {
        $this->expectException(ValidationException::class);
        new Client('   ');
    }

    public function testAcceptsCustomBaseUrl(): void
    {
        $client = new Client('sec4_k', 'https://custom.example.com/v1');
        $this->assertNotNull($client->email());
    }
}
