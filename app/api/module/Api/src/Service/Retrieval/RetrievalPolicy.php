<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

/**
 * The resolved delivery policy for one email flow: how strongly a link is gated and how long
 * it lives. Produced by {@see RetrievalPolicyResolver} from per-flow configuration.
 */
final class RetrievalPolicy
{
    public const GATE_NONE = 'none';
    public const GATE_OTP = 'otp';

    public function __construct(
        public readonly string $gate,
        public readonly int $expirySeconds,
    ) {
    }

    public function requiresOtp(): bool
    {
        return $this->gate === self::GATE_OTP;
    }

    /**
     * The absolute expiry for a link issued "now".
     */
    public function expiresFrom(\DateTimeImmutable $now): \DateTimeImmutable
    {
        return $now->add(new \DateInterval('PT' . $this->expirySeconds . 'S'));
    }
}
