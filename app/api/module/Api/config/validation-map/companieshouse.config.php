<?php

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
//  queries
   \Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse\ByNumber::class => NoValidationRequired::class
];
