<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Queries
    QueryHandler\Application\LicenceHistory::class         => Misc\CanAccessApplicationWithId::class,

    // Commands
    CommandHandler\Application\UpdateLicenceHistory::class => Misc\CanAccessApplicationWithId::class,
];
