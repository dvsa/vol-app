<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    CommandHandler\Audit\ReadApplication::class      => NoValidationRequired::class,
    CommandHandler\Audit\ReadBusReg::class           => NoValidationRequired::class,
    CommandHandler\Audit\ReadCase::class             => NoValidationRequired::class,
    CommandHandler\Audit\ReadLicence::class          => NoValidationRequired::class,
    CommandHandler\Audit\ReadOrganisation::class     => NoValidationRequired::class,
    CommandHandler\Audit\ReadTransportManager::class => NoValidationRequired::class,
    CommandHandler\Audit\ReadIrhpApplication::class  => NoValidationRequired::class,
    QueryHandler\Audit\ReadApplication::class        => IsInternalUser::class,
    QueryHandler\Audit\ReadBusReg::class             => IsInternalUser::class,
    QueryHandler\Audit\ReadCase::class               => IsInternalUser::class,
    QueryHandler\Audit\ReadLicence::class            => IsInternalUser::class,
    QueryHandler\Audit\ReadOrganisation::class       => IsInternalUser::class,
    QueryHandler\Audit\ReadTransportManager::class   => IsInternalUser::class,
    QueryHandler\Audit\ReadIrhpApplication::class    => IsInternalUser::class,
];
