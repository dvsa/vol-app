<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Commands
    CommandHandler\PrivateHireLicence\Create::class     => Handler\PrivateHireLicence\PrivateHireLicence::class,
    CommandHandler\PrivateHireLicence\CreateFactory::class     => Handler\PrivateHireLicence\PrivateHireLicence::class,
    CommandHandler\PrivateHireLicence\DeleteList::class => Handler\PrivateHireLicence\PrivateHireLicence::class,
    CommandHandler\PrivateHireLicence\Update::class     => Handler\PrivateHireLicence\PrivateHireLicence::class,
    CommandHandler\PrivateHireLicence\UpdateFactory::class     => Handler\PrivateHireLicence\PrivateHireLicence::class,
];
