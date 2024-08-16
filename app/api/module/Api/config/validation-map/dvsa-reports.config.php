<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    CommandHandler\DvsaReports\GetRedirect::class => NoValidationRequired::class,
    CommandHandler\DvsaReports\GetRedirectFactory::class => NoValidationRequired::class,

];
