<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\PsvDisc\Modify as ModifyPsvDisc;

return [
    // Queries
    QueryHandler\Licence\PsvDiscs::class            => Misc\CanAccessLicenceWithId::class,

    // Commands
    CommandHandler\Licence\CreatePsvDiscs::class    => Misc\CanAccessLicenceWithLicence::class,
    CommandHandler\Variation\CreatePsvDiscs::class  => Misc\CanAccessApplicationWithApplication::class,
    CommandHandler\Variation\VoidPsvDiscs::class    => ModifyPsvDisc::class,
    CommandHandler\Licence\VoidPsvDiscs::class      => ModifyPsvDisc::class,
    CommandHandler\Variation\ReplacePsvDiscs::class => ModifyPsvDisc::class,
    CommandHandler\Licence\ReplacePsvDiscs::class   => ModifyPsvDisc::class,
    CommandHandler\PsvDisc\ConfirmPrinting::class   => Misc\IsInternalUser::class,
    CommandHandler\PsvDisc\PrintDiscs::class        => Misc\IsInternalUser::class,
];
