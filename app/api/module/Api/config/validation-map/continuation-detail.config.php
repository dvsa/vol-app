<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\ContinuationDetail\CanAccessContinuationDetailWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemUser;

return [
    QueryHandler\ContinuationDetail\LicenceChecklist::class => CanAccessContinuationDetailWithId::class,
    QueryHandler\ContinuationDetail\Review::class => CanAccessContinuationDetailWithId::class,
    QueryHandler\ContinuationDetail\Get::class => CanAccessContinuationDetailWithId::class,
    CommandHandler\ContinuationDetail\UpdateFinances::class => CanAccessContinuationDetailWithId::class,
    CommandHandler\ContinuationDetail\UpdateInsufficientFinances::class => CanAccessContinuationDetailWithId::class,
    CommandHandler\ContinuationDetail\Submit::class => CanAccessContinuationDetailWithId::class,
    CommandHandler\ContinuationDetail\CreateSnapshot::class => IsSystemUser::class,
    CommandHandler\ContinuationDetail\DigitalSendReminders::class => IsSystemUser::class,
    CommandHandler\ContinuationDetail\GenerateChecklistReminder::class => IsSystemUser::class,
    CommandHandler\ContinuationDetail\GenerateChecklistDocument::class => IsSystemUser::class,
];
