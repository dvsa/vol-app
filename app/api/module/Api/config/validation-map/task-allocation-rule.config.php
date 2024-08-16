<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalAdmin;

return [
    QueryHandler\TaskAllocationRule\GetList::class      => IsInternalAdmin::class,
    QueryHandler\TaskAllocationRule\Get::class          => IsInternalAdmin::class,
    CommandHandler\TaskAllocationRule\DeleteList::class => IsInternalAdmin::class,
    CommandHandler\TaskAllocationRule\Create::class     => IsInternalAdmin::class,
    CommandHandler\TaskAllocationRule\Update::class     => IsInternalAdmin::class,

    QueryHandler\TaskAlphaSplit\GetList::class          => IsInternalAdmin::class,
    QueryHandler\TaskAlphaSplit\Get::class              => IsInternalAdmin::class,
    CommandHandler\TaskAlphaSplit\DeleteList::class     => IsInternalAdmin::class,
    CommandHandler\TaskAlphaSplit\Delete::class         => IsInternalAdmin::class,
    CommandHandler\TaskAlphaSplit\Create::class         => IsInternalAdmin::class,
    CommandHandler\TaskAlphaSplit\Update::class         => IsInternalAdmin::class,
];
