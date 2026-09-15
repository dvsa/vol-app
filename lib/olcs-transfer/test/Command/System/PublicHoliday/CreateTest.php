<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\System\PublicHoliday;

use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Create as CreateCommand;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Create::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Update::class)]
final class CreateTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals($holidayDate, $command->getHolidayDate());
        $this->assertEquals('Y', $command->getIsEngland());
        $this->assertEquals('N', $command->getIsWales());
        $this->assertEquals('N', $command->getIsScotland());
        $this->assertEquals('Y', $command->getIsIreland());
    }
}
