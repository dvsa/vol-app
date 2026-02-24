<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv::class)]
class ImportUsersFromCsvTest extends MockeryTestCase
{
    public function testStructure(): void
    {
        $sut = ImportUsersFromCsv::create(
            [
                'csvPath' => 'unit_sourceCsv',
                'resultCsvPath' => 'unit_resultCsv',
            ]
        );

        static::assertEquals('unit_sourceCsv', $sut->getCsvPath());
        static::assertEquals('unit_resultCsv', $sut->getResultCsvPath());
    }
}
