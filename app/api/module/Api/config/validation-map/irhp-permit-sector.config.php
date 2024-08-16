<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;

return [
    QueryHandler\IrhpPermitSector\GetList::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitSector\Update::class => IsInternalPermits::class,
];
