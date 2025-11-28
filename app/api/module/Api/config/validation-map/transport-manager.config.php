<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Queries
    QueryHandler\Application\TransportManagers::class => Misc\CanAccessApplicationWithId::class,
    QueryHandler\Licence\TransportManagers::class     => Misc\CanAccessLicenceWithId::class,

    QueryHandler\TransportManagerApplication\GetDetails::class             => Misc\CanAccessTmaWithId::class,
    QueryHandler\TransportManagerApplication\GetForResponsibilities::class => Misc\CanAccessTmaWithId::class,
    QueryHandler\TransportManagerApplication\Review::class                 => Misc\CanAccessTmaWithId::class,
    QueryHandler\TransportManagerApplication\GetList::class => Handler\TransportManagerApplication\GetList::class,

    QueryHandler\TransportManagerLicence\GetForResponsibilities::class     => Misc\CanAccessTmlWithId::class,
    QueryHandler\TransportManagerLicence\GetList::class            => Misc\CanAccessLicenceWithLicence::class,
    QueryHandler\TransportManagerLicence\GetListByVariation::class => Misc\CanAccessVariationWithVariation::class,

    QueryHandler\Tm\TransportManager::class                        => Misc\IsInternalUser::class,

    // Commands
    CommandHandler\TransportManagerApplication\Create::class => Handler\TransportManagerApplication\Create::class,
    CommandHandler\TransportManagerApplication\CreateForResponsibilities::class => Misc\IsInternalUser::class,
    CommandHandler\TransportManagerApplication\DeleteForResponsibilities::class => Misc\IsInternalUser::class,
    CommandHandler\TransportManagerApplication\UpdateForResponsibilities::class => Misc\IsInternalUser::class,
    CommandHandler\TransportManagerLicence\Delete::class => Handler\TransportManagerLicence\Delete::class,
    CommandHandler\TransportManagerLicence\DeleteForResponsibilities::class     => Misc\IsInternalUser::class,
    CommandHandler\TransportManagerLicence\UpdateForResponsibilities::class     => Misc\IsInternalUser::class,

    CommandHandler\TransportManagerApplication\OperatorSigned::class           => Misc\CanAccessTmaWithId::class,

    CommandHandler\TransportManagerApplication\Delete::class => Handler\TransportManagerApplication\Delete::class,
    CommandHandler\TransportManagerApplication\UpdateStatus::class  => Misc\CanAccessTmaWithId::class,
    CommandHandler\TransportManagerApplication\Submit::class        => Misc\CanAccessTmaWithId::class,
    CommandHandler\TransportManagerApplication\UpdateDetails::class => Misc\CanAccessTmaWithId::class,
    CommandHandler\TransportManagerApplication\UpdateDetailsFactory::class => Misc\CanAccessTmaWithId::class,
    CommandHandler\Variation\TransportManagerDeleteDelta::class     => Misc\CanAccessApplicationWithId::class,

    CommandHandler\Tm\CheckRepute::class => Misc\IsInternalEdit::class,
    CommandHandler\Tm\CheckReputeFactory::class => Misc\IsInternalEdit::class,
    CommandHandler\Tm\CreateNewUser::class                          => Misc\CanAccessApplicationWithApplication::class,
    CommandHandler\Tm\CreateNewUserFactory::class                   => Misc\CanAccessApplicationWithApplication::class,
    CommandHandler\Tm\UpdateNysiisName::class                       => Misc\IsSystemUser::class,

    CommandHandler\Email\SendTmApplication::class     => Misc\CanAccessTmaWithId::class,
    CommandHandler\Email\SendAmendTmApplication::class     => Misc\CanAccessTmaWithId::class,
    CommandHandler\TmEmployment\Create::class         => Handler\TmEmployment\Create::class,
    CommandHandler\TmEmployment\Update::class         => Handler\TmEmployment\Modify::class,
    CommandHandler\TmEmployment\DeleteList::class     => Handler\TmEmployment\ModifyList::class,
    QueryHandler\TmEmployment\GetSingle::class        => Misc\CanAccessTmEmploymentWithId::class,
];
