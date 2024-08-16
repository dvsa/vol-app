<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\CorrespondenceInbox\CanAccessCorrespondenceInboxWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationWithOrganisation;

return [
    CommandHandler\Correspondence\AccessCorrespondence::class => CanAccessCorrespondenceInboxWithId::class,
    QueryHandler\Correspondence\Correspondence::class => CanAccessCorrespondenceInboxWithId::class,
    QueryHandler\Correspondence\Correspondences::class => CanAccessOrganisationWithOrganisation::class,
];
