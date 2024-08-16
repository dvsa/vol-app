<?php

use Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification\Delete;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Transfer\Command\Disqualification\Update;

return [

    Delete::class => IsInternalUser::class,
    Update::class => IsInternalUser::class,
];
