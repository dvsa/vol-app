<?php

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv
 */
class ImportUsersFromCsvTest extends MockeryTestCase
{
    public function testStructure()
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
