<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    CommandHandler\Variation\DeleteListConditionUndertaking::class => Misc\IsInternalUser::class,
    CommandHandler\Variation\UpdateConditionUndertaking::class     => Misc\IsInternalUser::class,
    CommandHandler\Variation\UpdateAddresses::class                => Misc\CanAccessApplicationWithId::class,
    CommandHandler\Variation\UpdateTypeOfLicence::class            => Misc\CanAccessApplicationWithId::class,
    QueryHandler\Variation\TypeOfLicence::class                    => Misc\CanAccessApplicationWithId::class,
];
