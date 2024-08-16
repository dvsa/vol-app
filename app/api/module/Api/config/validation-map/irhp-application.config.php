<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationWithOrganisation;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicence;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanEditIrhpApplicationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    CommandHandler\IrhpApplication\Create::class => CanAccessLicenceWithLicence::class,
    CommandHandler\IrhpApplication\CreateFull::class => IsInternalUser::class,
    CommandHandler\IrhpApplication\UpdateFull::class => CanEditIrhpApplicationWithId::class,
    CommandHandler\IrhpApplication\StoreSnapshot::class => IsSideEffect::class,
    CommandHandler\IrhpApplication\Expire::class => IsSideEffect::class,
    CommandHandler\IrhpApplication\UpdatePeriod::class => CanEditIrhpApplicationWithId::class,
    QueryHandler\IrhpApplication\AvailableLicences::class => CanAccessIrhpApplicationWithId::class,
    QueryHandler\IrhpApplication\ApplicationPathGroupList::class => IsInternalPermits::class,
    QueryHandler\IrhpApplication\RangesByIrhpApplication::class => IsInternalUser::class,
    QueryHandler\IrhpApplication\GetGrantability::class => IsInternalUser::class,
];
