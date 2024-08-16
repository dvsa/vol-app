<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    QueryHandler\Replacement\ById::class => IsSystemAdmin::class,
    QueryHandler\Replacement\GetList::class => IsSystemAdmin::class,
    CommandHandler\Replacement\Create::class => IsSystemAdmin::class,
    CommandHandler\Replacement\Update::class => IsSystemAdmin::class,
];
