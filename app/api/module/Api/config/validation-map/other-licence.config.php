<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Queries
    QueryHandler\OtherLicence\OtherLicence::class            => Misc\CanAccessOtherLicenceWithId::class,
    QueryHandler\OtherLicence\GetList::class                 => Handler\OtherLicence\Modify::class,

    // Commands
    CommandHandler\OtherLicence\CreateOtherLicence::class    => Misc\CanAccessApplicationWithApplication::class,
    CommandHandler\OtherLicence\UpdateOtherLicence::class    => Misc\CanAccessOtherLicenceWithId::class,
    CommandHandler\OtherLicence\DeleteOtherLicence::class    => Handler\OtherLicence\Modify::class,
    CommandHandler\OtherLicence\CreateForTma::class          => Misc\CanAccessTmaWithId::class,
    CommandHandler\OtherLicence\UpdateForTma::class          => Misc\CanAccessOtherLicenceWithId::class,
    CommandHandler\OtherLicence\CreatePreviousLicence::class => Misc\CanAccessTmaWithId::class,
];
