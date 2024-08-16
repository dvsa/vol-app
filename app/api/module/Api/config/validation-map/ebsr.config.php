<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsLocalAuthorityUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsOperatorUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanAccessEbsrSubmissionWithId;

return [
    QueryHandler\Bus\Ebsr\TxcInboxList::class                                  => IsLocalAuthorityUser::class,
    QueryHandler\Bus\Ebsr\EbsrSubmissionList::class                            => IsOperatorUser::class,
    QueryHandler\Bus\Ebsr\EbsrSubmission::class                                => CanAccessEbsrSubmissionWithId::class,
    QueryHandler\Bus\Ebsr\OrganisationUnprocessedList::class                   => IsOperatorUser::class,
    CommandHandler\Bus\Ebsr\QueuePacks::class                                  => IsOperatorUser::class
];
