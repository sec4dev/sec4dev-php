<?php

declare(strict_types=1);

namespace Sec4Dev\Model;

final class IPCheckResult
{
    public string $ip;
    public string $classification;
    public float $confidence;
    public IPSignals $signals;
    public IPNetwork $network;
    public IPGeo $geo;

    public function __construct(
        string $ip,
        string $classification,
        float $confidence,
        IPSignals $signals,
        IPNetwork $network,
        IPGeo $geo
    ) {
        $this->ip = $ip;
        $this->classification = $classification;
        $this->confidence = $confidence;
        $this->signals = $signals;
        $this->network = $network;
        $this->geo = $geo;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['ip'] ?? '',
            $data['classification'] ?? 'unknown',
            (float) ($data['confidence'] ?? 0.0),
            IPSignals::fromArray($data['signals'] ?? []),
            IPNetwork::fromArray($data['network'] ?? []),
            IPGeo::fromArray($data['geo'] ?? [])
        );
    }
}
