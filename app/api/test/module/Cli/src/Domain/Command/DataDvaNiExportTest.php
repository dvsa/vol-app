<?php

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport
 */
class DataDvaNiExportTest extends MockeryTestCase
{
    public function test()
    {
        $params = [
            'reportName' => 'unit_ReportName',
            'path' => 'unit_Path',
        ];

        $sut = DataDvaNiExport::create($params);

        static::assertEquals('unit_ReportName', $sut->getReportName());
        static::assertEquals('unit_Path', $sut->getPath());
    }
}
