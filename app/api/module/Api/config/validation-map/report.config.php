<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    CommandHandler\Report\Upload::class => IsSystemAdmin::class,
];
