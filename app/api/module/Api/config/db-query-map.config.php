<?php

use Dvsa\Olcs\Api\Domain\Repository\Query;

return [
    'factories' => [
        'LicenceVehicle\CeaseDiscsForLicence' => Query\LicenceVehicle\CeaseDiscsForLicence::class,
        'LicenceVehicle\CeaseDiscsForApplication' => Query\LicenceVehicle\CeaseDiscsForApplication::class,
        'LicenceVehicle\CeaseDiscsForLicenceVehicle' => Query\LicenceVehicle\CeaseDiscsForLicenceVehicle::class,
        'LicenceVehicle\CreateDiscsForLicence' => Query\LicenceVehicle\CreateDiscsForLicence::class,
        'CommunityLicence\ExpireAllForLicence' => Query\CommunityLicence\ExpireAllForLicence::class,
        'LicenceVehicle\ClearSpecifiedDateAndInterimAppForLicence'
            => Query\LicenceVehicle\ClearSpecifiedDateAndInterimAppForLicence::class,
        'LicenceVehicle\RemoveAllForLicence' => Query\LicenceVehicle\RemoveAllForLicence::class,
        'LicenceVehicle\MarkDuplicateVrmsForLicence' => Query\LicenceVehicle\MarkDuplicateVrmsForLicence::class,
        'LicenceVehicle\ClearVehicleSection26' => Query\LicenceVehicle\ClearVehicleSection26::class,
        'Discs\CeaseDiscsForLicence' => Query\Discs\CeaseDiscsForLicence::class,
        'Discs\CreatePsvDiscs' => Query\Discs\CreatePsvDiscs::class,
        'Discs\GoodsDiscsSetIsPrinting' => Query\Discs\GoodsDiscsSetIsPrinting::class,
        'Discs\GoodsDiscsSetIsPrintingOffAndDiscNo' => Query\Discs\GoodsDiscsSetIsPrintingOffAndDiscNo::class,
        'Discs\PsvDiscsSetIsPrinting' => Query\Discs\PsvDiscsSetIsPrinting::class,
        'Discs\PsvDiscsSetIsPrintingOffAndDiscNo' => Query\Discs\PsvDiscsSetIsPrintingOffAndDiscNo::class,
        'Discs\CeaseGoodsDiscsForApplication' => Query\Discs\CeaseGoodsDiscsForApplication::class,
        'Discs\CreateGoodsDiscs' => Query\Discs\CreateGoodsDiscs::class,
        'ViStoredProcedures\ViOcComplete' => Query\ViStoredProcedures\ViOcComplete::class,
        'ViStoredProcedures\ViOpComplete' => Query\ViStoredProcedures\ViOpComplete::class,
        'ViStoredProcedures\ViTnmComplete' => Query\ViStoredProcedures\ViTnmComplete::class,
        'ViStoredProcedures\ViVhlComplete' => Query\ViStoredProcedures\ViVhlComplete::class,
        'Continuations\CreateContinuationDetails' => Query\Continuations\CreateContinuationDetails::class,
        'EventHistory\GetEventHistoryDetails' => Query\EventHistory\GetEventHistoryDetails::class,
        'Task\FlagUrgentTasks' => Query\Task\FlagUrgentTasks::class,
        Query\Bus\Expire::class => Query\Bus\Expire::class,
        Query\Organisation\FixIsIrfo::class => Query\Organisation\FixIsIrfo::class,
        Query\Organisation\FixIsUnlicenced::class => Query\Organisation\FixIsUnlicenced::class,
        Query\Licence\InternationalGoodsReport::class => Query\Licence\InternationalGoodsReport::class,
        Query\Permits\ExpireIrhpPermits::class => Query\Permits\ExpireIrhpPermits::class,
        Query\Permits\ExpireIrhpApplications::class => Query\Permits\ExpireIrhpApplications::class,
    ]
];
