<?php

declare(strict_types=1);

namespace Sec4Dev\Model;

final class IPNetwork
{
    public ?int $asn;
    public ?string $org;
    public ?string $provider;

    public function __construct(?int $asn = null, ?string $org = null, ?string $provider = null)
    {
        $this->asn = $asn;
        $this->org = $org;
        $this->provider = $provider;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['asn']) ? (int) $data['asn'] : null,
            $data['org'] ?? null,
            $data['provider'] ?? null
        );
    }
}
