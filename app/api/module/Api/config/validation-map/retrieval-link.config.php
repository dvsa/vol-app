<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

/*
 * Retrieve-via-Link is an anonymous journey: there is no authenticated user identity to authorise
 * against, so DTO-level validation is intentionally open. Each handler enforces its own access
 * control internally — the opaque link token must match a live, unrevoked, unexpired link, the
 * bundle member must belong to that link, and otp-gated downloads additionally require a valid
 * one-time-code session grant. See RetrievalLinkAccessTrait / SessionGrantService.
 */
return [
    QueryHandler\RetrievalLink\Resolve::class        => NoValidationRequired::class,
    QueryHandler\RetrievalLink\Download::class       => NoValidationRequired::class,
    CommandHandler\RetrievalLink\RequestOtp::class   => NoValidationRequired::class,
    CommandHandler\RetrievalLink\VerifyOtp::class    => NoValidationRequired::class,
    CommandHandler\RetrievalLink\PurgeExpired::class => NoValidationRequired::class,
];
