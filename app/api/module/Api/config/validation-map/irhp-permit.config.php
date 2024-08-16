<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicence;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;

return [
    QueryHandler\IrhpPermit\ById::class => IsInternalUser::class,
    QueryHandler\IrhpPermit\GetListByIrhpId::class => IsInternalUser::class,
    CommandHandler\IrhpPermit\GenerateCoverLetterDocument::class => IsSideEffect::class,
    CommandHandler\IrhpPermit\GeneratePermitDocument::class => IsSideEffect::class,
    CommandHandler\IrhpPermit\Replace::class => IsInternalUser::class,
    CommandHandler\IrhpPermit\Terminate::class => IsInternalUser::class,
    CommandHandler\IrhpPermit\CreateReplacement::class => IsSideEffect::class,
    QueryHandler\IrhpPermit\ByPermitNumber::class => NotIsAnonymousUser::class,
    QueryHandler\IrhpPermitRange\ByPermitNumber::class => NotIsAnonymousUser::class,
    QueryHandler\IrhpPermit\GetListByLicence::class => CanAccessLicenceWithLicence::class,
    QueryHandler\IrhpPermit\UniqueCountriesByLicence::class => CanAccessLicenceWithLicence::class,
];
