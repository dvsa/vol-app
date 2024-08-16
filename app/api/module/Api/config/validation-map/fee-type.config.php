<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    CommandHandler\FeeType\Update::class => IsSystemAdmin::class,
    QueryHandler\FeeType\GetList::class => IsSystemAdmin::class,
    QueryHandler\FeeType\GetDistinctList::class => IsSystemAdmin::class,
];
