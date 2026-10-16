<?php

declare(strict_types=1);

namespace Olcs\Service\GovUkAccount;

enum CallbackClaimStatus
{
    /** Nobody had seen this code; this request owns it. */
    case Claimed;

    /** Same user, and the request that owns the code has not finished yet. */
    case ReplayInFlight;

    /** Same user, and the owning request published where it sent them. */
    case ReplayComplete;

    /** A different user holds this code, or ownership could not be established. */
    case ForeignReplay;
}
