<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalEdit;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanEditApplicationWithId;

return [
    CommandHandler\Application\Grant\ProcessApplicationOperatingCentres::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\Grant::class                                    => CanEditApplicationWithId::class,
    CommandHandler\Application\GrantInterim::class                             => CanAccessApplicationWithId::class,
    CommandHandler\Variation\Grant::class                                      => CanEditApplicationWithId::class,
    CommandHandler\Variation\AutoGrant::class                                  => CanEditApplicationWithId::class,
    CommandHandler\Variation\GrantDirectorChange::class                        => CanEditApplicationWithId::class,
    CommandHandler\Variation\DeleteVariation::class                            => CanEditApplicationWithId::class,
    CommandHandler\Application\UndoGrant::class                                => IsInternalEdit::class,
    QueryHandler\Application\Grant::class                                      => IsInternalUser::class,
];
