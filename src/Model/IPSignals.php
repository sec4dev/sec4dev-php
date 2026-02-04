<?php

declare(strict_types=1);

namespace Sec4Dev\Model;

final class IPSignals
{
    public bool $isHosting;
    public bool $isResidential;
    public bool $isMobile;
    public bool $isVpn;
    public bool $isTor;
    public bool $isProxy;

    public function __construct(
        bool $isHosting = false,
        bool $isResidential = false,
        bool $isMobile = false,
        bool $isVpn = false,
        bool $isTor = false,
        bool $isProxy = false
    ) {
        $this->isHosting = $isHosting;
        $this->isResidential = $isResidential;
        $this->isMobile = $isMobile;
        $this->isVpn = $isVpn;
        $this->isTor = $isTor;
        $this->isProxy = $isProxy;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (bool) ($data['is_hosting'] ?? false),
            (bool) ($data['is_residential'] ?? false),
            (bool) ($data['is_mobile'] ?? false),
            (bool) ($data['is_vpn'] ?? false),
            (bool) ($data['is_tor'] ?? false),
            (bool) ($data['is_proxy'] ?? false)
        );
    }
}
