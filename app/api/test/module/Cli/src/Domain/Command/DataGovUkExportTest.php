<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport
 */
class DataGovUkExportTest extends MockeryTestCase
{
    public function test(): void
    {
        $params = [
            'reportName' => 'unit_ReportName',
            'path' => 'unit_Path',
        ];

        $sut = DataGovUkExport::create($params);

        static::assertEquals('unit_ReportName', $sut->getReportName());
    }
}
