<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;

return [
    QueryHandler\IrhpPermitJurisdiction\GetList::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitJurisdiction\Update::class => IsInternalPermits::class,
];
