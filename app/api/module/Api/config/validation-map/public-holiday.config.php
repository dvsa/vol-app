<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    CommandHandler\System\PublicHoliday\Create::class => IsSystemAdmin::class,
    CommandHandler\System\PublicHoliday\Update::class => IsSystemAdmin::class,
    CommandHandler\System\PublicHoliday\Delete::class => IsSystemAdmin::class,
    QueryHandler\System\PublicHoliday\Get::class => IsInternalAdmin::class,
    QueryHandler\System\PublicHoliday\GetList::class => IsInternalAdmin::class,
];
