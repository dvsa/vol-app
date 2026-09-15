<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport::class)]
final class DataDvaNiExportTest extends MockeryTestCase
{
    public function test(): void
    {
        $params = [
            'reportName' => 'unit_ReportName',
            'path' => 'unit_Path',
        ];

        $sut = DataDvaNiExport::create($params);

        $this->assertEquals('unit_ReportName', $sut->getReportName());
    }
}
