<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationWithApplication;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationWithOrganisation as OrgByOrg;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicence;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\Application as AppHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceVehicleWithId as LicenceVehicleById;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceVehiclesWithIds as LicenceVehicleByIds;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\CanTransfer;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalOrSystemUser;

return [
    CommandHandler\Application\CreateGoodsVehicle::class        => CanAccessApplicationWithId::class,
    CommandHandler\Application\CreatePsvVehicle::class          => CanAccessApplicationWithApplication::class,
    CommandHandler\Application\CreateVehicleListDocument::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\DeleteGoodsVehicle::class        => AppHandler\ModifyList::class,
    CommandHandler\Application\DeletePsvVehicle::class          => AppHandler\ModifyList::class,
    CommandHandler\Application\UpdateGoodsVehicle::class        => AppHandler\Modify::class,
    CommandHandler\Application\UpdatePsvVehicles::class         => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateVehicleDeclaration::class  => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateVehicles::class            => CanAccessApplicationWithId::class,

    CommandHandler\LicenceVehicle\CreateUnlicensedOperatorLicenceVehicle::class => OrgByOrg::class,
    CommandHandler\LicenceVehicle\DeleteUnlicensedOperatorLicenceVehicle::class => LicenceVehicleById::class,
    CommandHandler\LicenceVehicle\UpdatePsvLicenceVehicle::class                => LicenceVehicleById::class,
    CommandHandler\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle::class => LicenceVehicleById::class,

    CommandHandler\Licence\CreateGoodsVehicle::class        => CanAccessLicenceWithId::class,
    CommandHandler\Licence\CreatePsvVehicle::class          => CanAccessLicenceWithLicence::class,
    CommandHandler\Licence\CreateVehicleListDocument::class => IsInternalOrSystemUser::class,
    CommandHandler\Licence\TransferVehicles::class          => CanTransfer::class,
    CommandHandler\Licence\UpdateVehicles::class            => CanAccessLicenceWithId::class,
    CommandHandler\Vehicle\DeleteLicenceVehicle::class      => LicenceVehicleByIds::class,
    CommandHandler\Vehicle\ReprintDisc::class               => LicenceVehicleByIds::class,
    CommandHandler\Vehicle\UpdateGoodsVehicle::class        => LicenceVehicleById::class,
    CommandHandler\Vehicle\UpdateSection26::class           => IsInternalUser::class,

    QueryHandler\Application\GoodsVehicles::class          => CanAccessApplicationWithId::class,
    QueryHandler\Application\GoodsVehiclesExport::class    => CanAccessApplicationWithId::class,
    QueryHandler\Application\PsvVehicles::class            => CanAccessApplicationWithId::class,
    QueryHandler\Application\VehicleDeclaration::class     => CanAccessApplicationWithId::class,
    QueryHandler\LicenceVehicle\LicenceVehicle::class      => LicenceVehicleById::class,
    QueryHandler\LicenceVehicle\PsvLicenceVehicle::class   => LicenceVehicleById::class,
    QueryHandler\LicenceVehicle\LicenceVehiclesById::class => LicenceVehicleByIds::class,
    QueryHandler\Licence\GoodsDiscCount::class             => CanAccessLicenceWithId::class,
    QueryHandler\Licence\GoodsVehicles::class              => CanAccessLicenceWithId::class,
    QueryHandler\Licence\GoodsVehiclesExport::class        => CanAccessLicenceWithId::class,
    QueryHandler\Licence\PsvDiscCount::class               => CanAccessLicenceWithId::class,
    QueryHandler\Licence\PsvVehicles::class                => CanAccessLicenceWithId::class,
    QueryHandler\Licence\PsvVehiclesExport::class          => CanAccessLicenceWithId::class,
    QueryHandler\Licence\Vehicles::class                   => CanAccessLicenceWithId::class,
    QueryHandler\Operator\UnlicensedVehicles::class        => OrgByOrg::class,
    QueryHandler\Variation\GoodsVehicles::class            => CanAccessApplicationWithId::class,
    QueryHandler\Variation\GoodsVehiclesExport::class      => CanAccessApplicationWithId::class,
    QueryHandler\Variation\PsvVehicles::class              => CanAccessApplicationWithId::class,
];
