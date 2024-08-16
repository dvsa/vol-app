<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanSurrenderLicence;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Surrender\Delete as CanDeleteSurrender;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Surrender\CanAccessLicenceForSurrender;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Surrender\CanConfirmSurrender;

return [
    CommandHandler\Surrender\Approve::class                             => IsInternalUser::class,
    CommandHandler\Surrender\Clear::class                               => IsInternalUser::class,
    CommandHandler\Surrender\Create::class                              => CanSurrenderLicence::class,
    CommandHandler\Surrender\Delete::class                              => CanDeleteSurrender::class,
    QueryHandler\Surrender\GetSignature::class                          => CanConfirmSurrender::class,
    CommandHandler\Surrender\Withdraw::class                            => IsInternalUser::class,
    CommandHandler\Surrender\Snapshot::class                            => CanAccessLicenceWithId::class,
    CommandHandler\Surrender\SubmitForm::class                          => CanAccessLicenceForSurrender::class,
    CommandHandler\Surrender\Update::class                              => CanAccessLicenceForSurrender::class,
    QueryHandler\Surrender\ByLicence::class                             => CanAccessLicenceForSurrender::class,
    QueryHandler\Surrender\OpenBusReg::class                            => IsInternalUser::class,
    QueryHandler\Surrender\OpenCases::class                             => IsInternalUser::class,
    QueryHandler\Surrender\PreviousLicenceStatus::class                 => CanAccessLicenceWithId::class,

];
