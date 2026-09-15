<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport::class)]
final class CompanyHouseVsOlcsDiffsExportTest extends MockeryTestCase
{
    public function test(): void
    {
        $params = [
            'path' => 'unit_Path',
        ];

        $sut = CompaniesHouseVsOlcsDiffsExport::create($params);

        $this->assertEquals('unit_Path', $sut->getPath());
    }
}
