<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;

return [
    QueryHandler\IrhpPermitWindow\ById::class => IsInternalPermits::class,
    QueryHandler\IrhpPermitWindow\GetList::class => IsInternalPermits::class,
    QueryHandler\IrhpPermitWindow\OpenByType::class => IsInternalUser::class,
    CommandHandler\IrhpPermitWindow\Create::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitWindow\Update::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitWindow\Delete::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitWindow\Close::class => IsSideEffect::class,
];
