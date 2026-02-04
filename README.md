# Sec4Dev PHP SDK

Official PHP client for the [Sec4Dev](https://sec4.dev) Security Checks API. Check disposable emails and classify IP addresses (hosting, VPN, TOR, residential, etc.).

## Requirements

- PHP 8.0+
- [Composer](https://getcomposer.org/)
- Guzzle HTTP (installed via Composer)

## Installation

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

## Quick Start

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

## Configuration

```php
$client = new Client(
    apiKey: 'sec4_your_api_key',
    baseUrl: 'https://api.sec4.dev/api/v1',  // optional
    timeout: 30000,   // milliseconds, optional
    retries: 3,       // optional
    retryDelay: 1000, // milliseconds, optional
    onRateLimit: function (Sec4Dev\Model\RateLimitInfo $info) {
        // Called after each request with rate limit headers
    }
);
```

## Email API

- **`$client->email()->check(string $email): EmailCheckResult`**  
  Returns full result with `email`, `domain`, `isDisposable`.

- **`$client->email()->isDisposable(string $email): bool`**  
  Returns only whether the domain is disposable.

## IP API

- **`$client->ip()->check(string $ip): IPCheckResult`**  
  Returns full result: `ip`, `classification`, `confidence`, `signals`, `network`, `geo`.

- **`$client->ip()->isHosting(string $ip): bool`**
- **`$client->ip()->isVpn(string $ip): bool`**
- **`$client->ip()->isTor(string $ip): bool`**
- **`$client->ip()->isResidential(string $ip): bool`**
- **`$client->ip()->isMobile(string $ip): bool`**

## Rate limit info

```php
$client->email()->check('user@example.com');
$rateLimit = $client->getRateLimit();
echo "Remaining: {$rateLimit->remaining}\n";
```

## Exceptions

| Exception | HTTP | Description |
|-----------|------|-------------|
| `AuthenticationException` | 401 | Invalid or missing API key |
| `PaymentRequiredException` | 402 | Quota exceeded |
| `ForbiddenException` | 403 | Account deactivated |
| `NotFoundException` | 404 | Resource not found |
| `ValidationException` | 422 | Invalid input (email/IP format) |
| `RateLimitException` | 429 | Rate limit exceeded (`retryAfter`, `limit`, `remaining`) |
| `ServerException` | 5xx | Server error |
| `Sec4DevException` | - | Base exception |

## Running tests

```bash
composer install
composer test
```

## License

MIT
