<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Submission as SubmissionQueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;

return [
    // Queries
    QueryHandler\Submission\Submission::class               => Handler\Misc\IsInternalUser::class,
    QueryHandler\Submission\SubmissionAction::class         => Handler\Misc\IsInternalUser::class,
    QueryHandler\Submission\SubmissionList::class           => Handler\Misc\IsInternalUser::class,
    QueryHandler\Submission\SubmissionSectionComment::class => Handler\Misc\IsInternalUser::class,

    // Commands
    CommandHandler\Submission\CloseSubmission::class                => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\ReopenSubmission::class               => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\AssignSubmission::class               => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\InformationComplete::class            => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\CreateSubmission::class               => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\CreateSubmissionAction::class         => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\CreateSubmissionSectionComment::class => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\DeleteSubmissionSectionComment::class => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\FilterSubmissionSections::class       => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\RefreshSubmissionSections::class      => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\UpdateSubmission::class               => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\UpdateSubmissionAction::class         => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\UpdateSubmissionSectionComment::class => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\DeleteSubmission::class               => Handler\Misc\IsInternalUser::class,
    CommandHandler\Submission\StoreSubmissionSnapshot::class        => Handler\Misc\IsInternalUser::class,
];
