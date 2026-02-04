# Sec4Dev PHP SDK

PHP client for the [Sec4Dev Security Checks API](https://api.sec4.dev): disposable email detection and IP classification.

## Documentation

Full API documentation: [https://docs.sec4.dev/](https://docs.sec4.dev/)

## Requirements

- PHP 8.0+
- [Composer](https://getcomposer.org/)

## Install

```bash
composer require sec4dev/php
```

Or add to your `composer.json`:

```json
{
    "require": {
        "sec4dev/php": "^1.0"
    }
}
```

## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sec4Dev\Client;
use Sec4Dev\Exception\ValidationException;
use Sec4Dev\Exception\RateLimitException;

$client = new Client('sec4_your_api_key');

// Email check
try {
    $result = $client->email()->check('user@tempmail.com');
    if ($result->isDisposable) {
        echo "Blocked: {$result->domain} is a disposable domain\n";
    }
} catch (ValidationException $e) {
    echo "Invalid email: {$e->getMessage()}\n";
}

// IP check
try {
    $result = $client->ip()->check('203.0.113.42');
    echo "Classification: {$result->classification}\n";
    echo "Confidence: " . round($result->confidence * 100) . "%\n";
    if ($result->signals->isHosting) {
        echo "Provider: {$result->network->provider}\n";
    }
} catch (RateLimitException $e) {
    echo "Rate limited. Retry in {$e->retryAfter}s\n";
}
```

## Options

Constructor parameters:

- `apiKey` — API key (required, must start with `sec4_`)
- `baseUrl` — API base URL (default: `https://api.sec4.dev/api/v1`)
- `timeout` — Request timeout in ms (default: 30000)
- `retries` — Retry attempts (default: 3)
- `retryDelay` — Base retry delay in ms (default: 1000)
- `onRateLimit` — Optional callback for rate limit updates

Example:

```php
$client = new Client(
    apiKey: 'sec4_your_api_key',
    baseUrl: 'https://api.sec4.dev/api/v1',
    timeout: 30000,
    retries: 3,
    retryDelay: 1000,
    onRateLimit: function (Sec4Dev\Model\RateLimitInfo $info) {
        // Called after each request with rate limit headers
    }
);
```

## Exceptions

| Exception                  | HTTP | Description                    |
|---------------------------|------|--------------------------------|
| `AuthenticationException` | 401  | Invalid or missing API key     |
| `PaymentRequiredException`| 402  | Quota exceeded                 |
| `ForbiddenException`      | 403  | Account deactivated            |
| `NotFoundException`      | 404  | Resource not found             |
| `ValidationException`     | 422  | Invalid input (email/IP format)|
| `RateLimitException`      | 429  | Rate limit exceeded            |
| `ServerException`         | 5xx  | Server error                   |
| `Sec4DevException`        | -    | Base exception                 |

## Testing

```bash
composer install
composer test
```
