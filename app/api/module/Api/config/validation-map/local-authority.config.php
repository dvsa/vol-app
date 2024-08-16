<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    // Queries
    QueryHandler\LocalAuthority\LocalAuthorityList::class => IsInternalUser::class,
    QueryHandler\LocalAuthority\ById::class => IsSystemAdmin::class,
    // Commands
    CommandHandler\LocalAuthority\Update::class => IsSystemAdmin::class
];
