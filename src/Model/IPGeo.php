<?php

declare(strict_types=1);

namespace Sec4Dev\Model;

final class IPGeo
{
    public ?string $country;
    public ?string $region;

    public function __construct(?string $country = null, ?string $region = null)
    {
        $this->country = $country;
        $this->region = $region;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['country'] ?? null,
            $data['region'] ?? null
        );
    }
}
