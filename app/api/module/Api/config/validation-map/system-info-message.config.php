<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    CommandHandler\System\InfoMessage\Create::class => IsSystemAdmin::class,
    CommandHandler\System\InfoMessage\Update::class => IsSystemAdmin::class,
    CommandHandler\System\InfoMessage\Delete::class => IsSystemAdmin::class,
    QueryHandler\System\InfoMessage\Get::class => IsInternalAdmin::class,
    QueryHandler\System\InfoMessage\GetList::class => IsInternalAdmin::class,
    QueryHandler\System\InfoMessage\GetListActive::class => NoValidationRequired::class,
];
