<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanManageUserInternal;

return [
    QueryHandler\TeamPrinter\TeamPrinterExceptionsList::class               => IsInternalUser::class,
    QueryHandler\TeamPrinter\TeamPrinter::class                             => IsInternalUser::class,
    CommandHandler\TeamPrinter\CreateTeamPrinter::class                     => CanManageUserInternal::class,
    CommandHandler\TeamPrinter\UpdateTeamPrinter::class                     => CanManageUserInternal::class,
    CommandHandler\TeamPrinter\DeleteTeamPrinter::class                     => CanManageUserInternal::class,
];
