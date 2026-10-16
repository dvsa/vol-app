<?php

declare(strict_types=1);

namespace Olcs\Service\GovUkAccount;

/**
 * The outcome of trying to claim a GOV.UK One Login authorisation code.
 */
final readonly class CallbackClaim
{
    private function __construct(
        public CallbackClaimStatus $status,
        public ?string $redirectUrl,
    ) {
    }

    public static function claimed(): self
    {
        return new self(CallbackClaimStatus::Claimed, null);
    }

    public static function replayInFlight(): self
    {
        return new self(CallbackClaimStatus::ReplayInFlight, null);
    }

    public static function replayComplete(string $redirectUrl): self
    {
        return new self(CallbackClaimStatus::ReplayComplete, $redirectUrl);
    }

    public static function foreignReplay(): self
    {
        return new self(CallbackClaimStatus::ForeignReplay, null);
    }

    /** True only for a replay by the user who owns the code. */
    public function isOwnReplay(): bool
    {
        return $this->status === CallbackClaimStatus::ReplayInFlight
            || $this->status === CallbackClaimStatus::ReplayComplete;
    }

    /** Only the owning request may publish an outcome. */
    public function ownsCode(): bool
    {
        return $this->status === CallbackClaimStatus::Claimed;
    }
}
