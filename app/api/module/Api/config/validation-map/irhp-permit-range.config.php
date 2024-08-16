<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;

return [
    QueryHandler\IrhpPermitRange\ById::class => IsInternalPermits::class,
    QueryHandler\IrhpPermitRange\GetList::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitRange\Create::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitRange\Update::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitRange\Delete::class => IsInternalPermits::class,
];
