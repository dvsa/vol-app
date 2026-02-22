<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport
 */
class CompanyHouseVsOlcsDiffsExportTest extends MockeryTestCase
{
    public function test(): void
    {
        $params = [
            'path' => 'unit_Path',
        ];

        $sut = CompaniesHouseVsOlcsDiffsExport::create($params);

        static::assertEquals('unit_Path', $sut->getPath());
    }
}
