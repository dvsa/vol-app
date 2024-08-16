<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    CommandHandler\System\CreateFinancialStandingRate::class     => IsSystemAdmin::class,
    CommandHandler\System\DeleteFinancialStandingRateList::class => IsSystemAdmin::class,
    CommandHandler\System\UpdateFinancialStandingRate::class     => IsSystemAdmin::class,
];
