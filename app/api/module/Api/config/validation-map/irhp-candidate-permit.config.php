<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithIrhpApplication;

return [
    QueryHandler\IrhpCandidatePermit\ById::class => IsInternalUser::class,
    QueryHandler\IrhpCandidatePermit\GetListByIrhpApplication::class
        => CanAccessIrhpApplicationWithIrhpApplication::class,
    CommandHandler\IrhpCandidatePermit\Delete::class => IsInternalUser::class,
    CommandHandler\IrhpCandidatePermit\Update::class => IsInternalUser::class,
    CommandHandler\IrhpCandidatePermit\Create::class => IsInternalUser::class,
];
