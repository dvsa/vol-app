<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanGovUkAccount;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    CommandHandler\GovUkAccount\GetGovUkAccountRedirect::class => CanGovUkAccount::class,
    CommandHandler\GovUkAccount\GetGovUkAccountRedirectFactory::class => NoValidationRequired::class,
    CommandHandler\GovUkAccount\ProcessAuthResponse::class => CanGovUkAccount::class,
    CommandHandler\GovUkAccount\ProcessAuthResponseFactory::class => NoValidationRequired::class,
];
