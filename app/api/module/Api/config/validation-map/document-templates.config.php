<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    //  queries
    QueryHandler\DocTemplate\FullList::class => IsSystemAdmin::class,
    QueryHandler\DocTemplate\ById::class => IsSystemAdmin::class,

    //  commands
    CommandHandler\DocTemplate\Create::class => IsSystemAdmin::class,
    CommandHandler\DocTemplate\Update::class => IsSystemAdmin::class,
    CommandHandler\DocTemplate\Delete::class => IsSystemAdmin::class
];
