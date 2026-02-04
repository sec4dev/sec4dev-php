<?php

declare(strict_types=1);

namespace Sec4Dev;

use Sec4Dev\Exception\ValidationException;

final class Validation
{
    private const EMAIL_REGEX = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    public static function validateEmail(?string $email): void
    {
        if ($email === null || $email === '') {
            throw new ValidationException('Email is required', 422);
        }
        $trimmed = trim($email);
        if ($trimmed === '') {
            throw new ValidationException('Email cannot be empty', 422);
        }
        if (!preg_match(self::EMAIL_REGEX, $trimmed)) {
            throw new ValidationException('Invalid email format', 422);
        }
    }

    public static function validateIp(?string $ip): void
    {
        if ($ip === null || $ip === '') {
            throw new ValidationException('IP address is required', 422);
        }
        $trimmed = trim($ip);
        if ($trimmed === '') {
            throw new ValidationException('IP address cannot be empty', 422);
        }
        if (filter_var($trimmed, FILTER_VALIDATE_IP) === false) {
            throw new ValidationException('Invalid IP address format', 422);
        }
    }
}
