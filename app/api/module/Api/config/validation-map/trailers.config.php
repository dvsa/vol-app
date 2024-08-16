<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Trailer\DeleteTrailer as DeleteTrailer;

return [
    // Queries
    QueryHandler\Licence\Trailers::class         => Misc\CanAccessLicenceWithId::class,
    QueryHandler\Trailer\Trailer::class          => Misc\CanAccessTrailerWithId::class,

    // Commands
    CommandHandler\Licence\UpdateTrailers::class => Misc\CanAccessLicenceWithId::class,
    CommandHandler\Trailer\CreateTrailer::class  => Misc\CanAccessLicenceWithLicence::class,
    CommandHandler\Trailer\DeleteTrailer::class  => DeleteTrailer::class,
    CommandHandler\Trailer\UpdateTrailer::class  => Misc\CanAccessTrailerWithId::class,
];
