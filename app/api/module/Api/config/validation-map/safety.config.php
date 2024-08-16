<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application as AppCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence as LicCommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    CommandHandler\Application\UpdateSafety::class   => Misc\CanAccessApplicationWithId::class,
    CommandHandler\Licence\UpdateSafety::class       => Misc\CanAccessLicenceWithId::class,
    QueryHandler\Application\Safety::class           => Misc\CanAccessApplicationWithId::class,
    QueryHandler\Licence\Safety::class               => Misc\CanAccessLicenceWithId::class,

    CommandHandler\Application\CreateWorkshop::class => Misc\CanAccessApplicationWithApplication::class,
    CommandHandler\Application\DeleteWorkshop::class => Handler\Workshop\Application\Modify::class,
    CommandHandler\Application\UpdateWorkshop::class => Handler\Workshop\Application\Update::class,

    CommandHandler\Workshop\CreateWorkshop::class    => Misc\CanAccessLicenceWithLicence::class,
    CommandHandler\Workshop\DeleteWorkshop::class    => Handler\Workshop\Licence\Modify::class,
    CommandHandler\Workshop\UpdateWorkshop::class    => Handler\Workshop\Licence\Update::class,

    QueryHandler\Workshop\Workshop::class            => Handler\Workshop\CanAccessApplicationOrLicenceWithId::class
];
