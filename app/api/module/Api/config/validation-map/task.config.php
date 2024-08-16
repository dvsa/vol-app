<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemUser;

return [
    CommandHandler\Task\FlagUrgentTasks::class => IsSystemUser::class,
];
