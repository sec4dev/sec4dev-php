<?php

declare(strict_types=1);

namespace Sec4Dev\Model;

final class EmailCheckResult
{
    public string $email;
    public string $domain;
    public bool $isDisposable;

    public function __construct(string $email, string $domain, bool $isDisposable)
    {
        $this->email = $email;
        $this->domain = $domain;
        $this->isDisposable = $isDisposable;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'] ?? '',
            $data['domain'] ?? '',
            (bool) ($data['is_disposable'] ?? false)
        );
    }
}
