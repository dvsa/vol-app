<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    //  queries
    QueryHandler\DocTemplate\FullList::class => IsSystemAdmin::class,
    QueryHandler\DocTemplate\ById::class => IsInternalUser::class,

    //  commands
    CommandHandler\DocTemplate\Create::class => IsSystemAdmin::class,
    CommandHandler\DocTemplate\Update::class => IsSystemAdmin::class,
    CommandHandler\DocTemplate\Delete::class => IsSystemAdmin::class
];
