<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    CommandHandler\SystemParameter\Create::class            => IsSystemAdmin::class,
    CommandHandler\SystemParameter\Update::class            => IsSystemAdmin::class,
    CommandHandler\SystemParameter\Delete::class            => IsSystemAdmin::class,
    QueryHandler\SystemParameter\SystemParameter::class     => IsInternalUser::class,
    QueryHandler\SystemParameter\SystemParameterList::class => IsInternalUser::class,
];
