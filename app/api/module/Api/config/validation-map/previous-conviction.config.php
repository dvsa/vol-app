<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;
use Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction as PreviousConvictionCommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\PreviousConviction\DeletePreviousConviction as
    DeletePreviousConvictionValidator;

return [
    CommandHandler\Application\UpdatePreviousConvictions::class => Misc\CanAccessApplicationWithId::class,
    QueryHandler\Application\PreviousConvictions::class => Misc\CanAccessApplicationWithId::class,

    PreviousConvictionCommandHandler\CreateForTma::class => Misc\CanAccessTmaWithId::class,
    PreviousConvictionCommandHandler\CreatePreviousConviction::class => Misc\CanAccessApplicationWithApplication::class,
    PreviousConvictionCommandHandler\DeletePreviousConviction::class => DeletePreviousConvictionValidator::class,
    PreviousConvictionCommandHandler\UpdatePreviousConviction::class => Misc\CanAccessPreviousConvictionWithId::class,

    QueryHandler\PreviousConviction\GetList::class => Misc\IsInternalUser::class,
    QueryHandler\PreviousConviction\PreviousConviction::class => Misc\CanAccessPreviousConvictionWithId::class,
];
