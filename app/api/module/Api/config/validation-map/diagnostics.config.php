<?php

use Dvsa\Olcs\Api\Domain\QueryHandler\Diagnostics\CheckFkIntegrity;
use Dvsa\Olcs\Api\Domain\QueryHandler\Diagnostics\GenerateCheckFkIntegritySql;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemUser;

return [
    CheckFkIntegrity::class => IsSystemUser::class,
    GenerateCheckFkIntegritySql::class => IsSystemUser::class,
];
