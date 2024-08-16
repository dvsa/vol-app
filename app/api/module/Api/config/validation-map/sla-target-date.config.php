<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;

return [
    // Queries
    QueryHandler\System\SlaTargetDate::class                           => Handler\Misc\IsInternalUser::class,

    // Commands
    CommandHandler\System\CreateSlaTargetDate::class                   => Handler\Misc\IsInternalUser::class,
    CommandHandler\System\UpdateSlaTargetDate::class                   => Handler\Misc\IsInternalUser::class,
];
