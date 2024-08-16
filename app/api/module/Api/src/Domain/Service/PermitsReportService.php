<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Service;

use Dvsa\Olcs\Api\Domain\Command\Permits\RoadworthinessReport;

class PermitsReportService
{
    public const REPORT_TYPES = [
        'cert_roadworthiness' => 'Certificate of Roadworthiness',
    ];

    public const COMMAND_MAP = [
        'cert_roadworthiness' => RoadworthinessReport::class
    ];
}
