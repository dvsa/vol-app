<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\TrafficArea\TrafficAreaInternalList::class => IsInternalUser::class,
    QueryHandler\TrafficArea\TrafficAreaList::class => NoValidationRequired::class,
    QueryHandler\TrafficArea\Get::class => IsInternalUser::class,
];
