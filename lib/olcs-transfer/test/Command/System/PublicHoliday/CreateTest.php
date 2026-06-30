<?php

namespace Dvsa\OlcsTest\Transfer\Command\System\PublicHoliday;

use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Create as CreateCommand;

/**
 * @covers Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Create
 * @covers Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Update
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $holidayDate = new \DateTime();

        $data = [
            'holidayDate' => $holidayDate,
            'isEngland' => 'Y',
            'isWales' => 'N',
            'isScotland' => 'N',
            'isNi' => 'Y',
        ];
        $command = CreateCommand::create($data);

        static::assertEquals($holidayDate, $command->getHolidayDate());
        static::assertEquals('Y', $command->getIsEngland());
        static::assertEquals('N', $command->getIsWales());
        static::assertEquals('N', $command->getIsScotland());
        static::assertEquals('Y', $command->getIsIreland());
    }
}
