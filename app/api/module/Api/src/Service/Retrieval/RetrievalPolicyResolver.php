<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

/**
 * Resolves an email flow's Retrieve-via-Link policy from configuration.
 *
 * Config shape (`config['retrieval']['policies']`):
 *   'publication'        => ['gate' => 'none', 'expiry' => 'P42D'],   // public record, 6 weeks
 *   'financial-evidence' => ['gate' => 'otp',  'expiry' => 'P3D'],    // DoB/bank data, 72h + OTP
 *
 * `expiry` accepts an ISO-8601 duration (e.g. "P42D", "PT72H") or an integer number of seconds.
 *
 * Fails secure: an unconfigured flow gets the STRONGEST posture (OTP + a short window), so
 * forgetting to add a policy can never accidentally publish a document without a gate.
 */
final class RetrievalPolicyResolver
{
    /** Fallback for an unknown flow: OTP-gated, 72 hours. */
    private const FALLBACK_GATE = RetrievalPolicy::GATE_OTP;
    private const FALLBACK_EXPIRY_SECONDS = 259200;

    /** Default window when a policy omits `expiry`: 6 weeks. */
    private const DEFAULT_EXPIRY = 'P42D';

    /** @var array<string, array{gate?: string, expiry?: string|int}> */
    private array $policies;

    /**
     * @param array<string, array{gate?: string, expiry?: string|int}> $policies
     */
    public function __construct(array $policies)
    {
        $this->policies = $policies;
    }

    public function resolve(string $flowKey): RetrievalPolicy
    {
        $config = $this->policies[$flowKey] ?? null;

        if ($config === null) {
            return new RetrievalPolicy(self::FALLBACK_GATE, self::FALLBACK_EXPIRY_SECONDS);
        }

        $gate = $config['gate'] ?? RetrievalPolicy::GATE_OTP;
        if (!in_array($gate, [RetrievalPolicy::GATE_NONE, RetrievalPolicy::GATE_OTP], true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid retrieval gate "%s" configured for flow "%s"', $gate, $flowKey),
            );
        }

        return new RetrievalPolicy($gate, $this->toSeconds($config['expiry'] ?? self::DEFAULT_EXPIRY, $flowKey));
    }

    /**
     * @param string|int $expiry ISO-8601 duration or an integer number of seconds
     */
    private function toSeconds(string|int $expiry, string $flowKey): int
    {
        if (is_int($expiry)) {
            if ($expiry <= 0) {
                throw new \InvalidArgumentException(sprintf('Retrieval expiry for flow "%s" must be positive', $flowKey));
            }
            return $expiry;
        }

        try {
            $interval = new \DateInterval($expiry);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Invalid retrieval expiry duration "%s" for flow "%s"', $expiry, $flowKey),
                0,
                $e,
            );
        }

        // Anchor the interval to a fixed epoch to convert calendar units (days/months) to seconds.
        $epoch = new \DateTimeImmutable('@0');
        return $epoch->add($interval)->getTimestamp();
    }
}
