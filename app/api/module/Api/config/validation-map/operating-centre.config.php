<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre as AppOcHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationOperatingCentreWithId as AocById;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceOperatingCentreWithId as LocById;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationWithApplication as AppByApp;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicence as LicByLic;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre\CanAccessRecordByTypeAndIdentifier as RecordByType;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre\CanAccessXocWithReference as XocByRef;

return [
    AppOcHandler\Update::class                                                => AocById::class,
    CommandHandler\Application\CreateOperatingCentre::class                   => AppByApp::class,
    CommandHandler\Application\DeleteOperatingCentres::class                  => AppByApp::class,
    CommandHandler\Application\UpdateOperatingCentres::class                  => Misc\CanAccessApplicationWithId::class,
    CommandHandler\LicenceOperatingCentre\Update::class                       => LocById::class,
    CommandHandler\Licence\CreateOperatingCentre::class                       => LicByLic::class,
    CommandHandler\Licence\DeleteOperatingCentres::class                      => LicByLic::class,
    CommandHandler\Licence\UpdateOperatingCentres::class                      => Misc\CanAccessLicenceWithId::class,
    QueryHandler\ApplicationOperatingCentre\ApplicationOperatingCentre::class => AocById::class,
    QueryHandler\Application\OperatingCentre::class                           => Misc\CanAccessApplicationWithId::class,
    QueryHandler\Application\OperatingCentres::class                          => Misc\CanAccessApplicationWithId::class,
    QueryHandler\LicenceOperatingCentre\LicenceOperatingCentre::class         => LocById::class,
    QueryHandler\Licence\OperatingCentre::class                               => Misc\CanAccessLicenceWithId::class,
    QueryHandler\Licence\OperatingCentres::class                              => Misc\CanAccessLicenceWithId::class,
    CommandHandler\VariationOperatingCentre\Update::class                     => XocByRef::class,
    CommandHandler\Variation\DeleteOperatingCentre::class                     => XocByRef::class,
    CommandHandler\Variation\RestoreOperatingCentre::class                    => XocByRef::class,
    QueryHandler\VariationOperatingCentre\VariationOperatingCentre::class     => XocByRef::class,
    QueryHandler\InspectionRequest\OperatingCentres::class                    => RecordByType::class,
];
