<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    QueryHandler\DataService\ApplicationStatus::class => Misc\IsInternalUser::class,
];
