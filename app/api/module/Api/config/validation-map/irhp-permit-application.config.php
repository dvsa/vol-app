<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;

return [
    CommandHandler\IrhpPermitApplication\CreateForIrhpApplication::class => IsSideEffect::class,
    CommandHandler\IrhpPermitApplication\UpdateIrhpPermitWindow::class => IsSideEffect::class,
];
