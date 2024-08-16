<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Queries
    QueryHandler\Organisation\Dashboard::class => Misc\CanAccessOrganisationWithId::class,
    QueryHandler\Organisation\Organisation::class => Misc\CanAccessOrganisationWithId::class,
    QueryHandler\Organisation\OrganisationAvailableLicences::class => Misc\CanAccessOrganisationWithId::class,
    QueryHandler\Organisation\OutstandingFees::class => Misc\CanAccessOrganisationWithId::class,
    QueryHandler\Organisation\BusinessDetails::class => Misc\IsInternalUser::class,
    QueryHandler\Organisation\CpidOrganisation::class => Misc\IsInternalUser::class,

    // Commands
    CommandHandler\Organisation\UpdateBusinessType::class => Misc\CanAccessOrganisationWithId::class,
    CommandHandler\Organisation\CpidOrganisationExport::class => Misc\IsInternalUser::class,
    CommandHandler\Organisation\TransferTo::class => Misc\IsInternalEdit::class,
    CommandHandler\Organisation\GenerateName::class => Misc\CanAccessApplicationWithApplication::class,
    CommandHandler\Organisation\FixIsIrfo::class => Misc\IsSystemUser::class,
    CommandHandler\Organisation\FixIsUnlicenced::class => Misc\IsSystemUser::class,
];
