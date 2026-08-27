<?php

declare(strict_types=1);

namespace Olcs\Service\GovUkAccount;

/**
 * The outcome of trying to claim a GOV.UK One Login authorisation code.
 */
final readonly class CallbackClaim
{
    private function __construct(
        public bool $isReplay,
        public ?string $redirectUrl,
    ) {
    }

    /** This request owns the code and should process it. */
    public static function claimed(): self
    {
        return new self(false, null);
    }

    /** An earlier request finished and sent the user to $redirectUrl. */
    public static function replayOf(string $redirectUrl): self
    {
        return new self(true, $redirectUrl);
    }

    /** An earlier request owns the code and has not published an outcome yet. */
    public static function replayInFlight(): self
    {
        return new self(true, null);
    }
}
