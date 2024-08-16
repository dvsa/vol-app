<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalOrSystemUser;

return [
    QueryHandler\Cpms\ReportStatus::class => IsInternalOrSystemUser::class,
    CommandHandler\Cpms\DownloadReport::class => IsInternalOrSystemUser::class,
];
