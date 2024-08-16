<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;

return [
    QueryHandler\IrhpPermitStock\ById::class => IsInternalPermits::class,
    QueryHandler\IrhpPermitStock\GetList::class => IsInternalPermits::class,
    QueryHandler\IrhpPermitStock\AvailableBilateral::class => NotIsAnonymousUser::class,
    CommandHandler\IrhpPermitStock\Create::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitStock\Update::class => IsInternalPermits::class,
    CommandHandler\IrhpPermitStock\Delete::class => IsInternalPermits::class,
];
