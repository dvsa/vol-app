<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    CommandHandler\FeatureToggle\Create::class => IsSystemAdmin::class,
    CommandHandler\FeatureToggle\Update::class => IsSystemAdmin::class,
    CommandHandler\FeatureToggle\Delete::class => IsSystemAdmin::class,
    QueryHandler\FeatureToggle\ById::class => IsInternalAdmin::class,
    QueryHandler\FeatureToggle\GetList::class => IsInternalAdmin::class,
    QueryHandler\FeatureToggle\IsEnabled::class => NoValidationRequired::class,
];
