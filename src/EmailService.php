<?php

declare(strict_types=1);

namespace Sec4Dev;

use Sec4Dev\Http\HttpClient;
use Sec4Dev\Model\EmailCheckResult;

final class EmailService
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    public function check(string $email): EmailCheckResult
    {
        Validation::validateEmail($email);
        [$data] = $this->http->post('/email/check', ['email' => trim($email)]);
        return EmailCheckResult::fromArray($data);
    }

    public function isDisposable(string $email): bool
    {
        return $this->check($email)->isDisposable;
    }
}
