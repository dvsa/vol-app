<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    // This has to be NO validation as it is called from kofax scanning client and isn't authenticated
    CommandHandler\Scan\CreateDocument::class => NoValidationRequired::class,
];
