<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;

return [
    //  commands
    CommandHandler\BulkSend\ProcessEmail::class => IsSideEffect::class,
];
