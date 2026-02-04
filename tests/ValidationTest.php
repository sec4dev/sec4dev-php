<?php

declare(strict_types=1);

namespace Sec4Dev\Tests;

use PHPUnit\Framework\TestCase;
use Sec4Dev\Exception\ValidationException;
use Sec4Dev\Validation;

final class ValidationTest extends TestCase
{
    public function testValidateEmailAcceptsValid(): void
    {
        Validation::validateEmail('user@example.com');
        Validation::validateEmail('a@b.co');
        Validation::validateEmail('  user@domain.org  ');
        $this->addToAssertionCount(3);
    }

    public function testValidateEmailRejectsEmpty(): void
    {
        $this->expectException(ValidationException::class);
        Validation::validateEmail('');
    }

    public function testValidateEmailRejectsInvalidFormat(): void
    {
        $this->expectException(ValidationException::class);
        Validation::validateEmail('not-an-email');
    }

    public function testValidateIpAcceptsValid(): void
    {
        Validation::validateIp('192.168.1.1');
        Validation::validateIp('::1');
        Validation::validateIp('203.0.113.42');
        $this->addToAssertionCount(3);
    }

    public function testValidateIpRejectsEmpty(): void
    {
        $this->expectException(ValidationException::class);
        Validation::validateIp('');
    }

    public function testValidateIpRejectsInvalid(): void
    {
        $this->expectException(ValidationException::class);
        Validation::validateIp('256.1.1.1');
    }
}
