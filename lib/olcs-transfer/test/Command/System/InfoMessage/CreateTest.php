<?php

namespace Dvsa\OlcsTest\Transfer\Command\System\InfoMessage;

use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Create as CreateCommand;

/**
 * @covers Dvsa\Olcs\Transfer\Command\System\InfoMessage\Create
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $startDate = (new \DateTime())->setTime(0, 0, 0);
        $endDate = (new \DateTime())->setTime(23, 59, 59);

        $data = [
            'description' => 'unit_Desc',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isInternal' => 'Y',
        ];
        $command = CreateCommand::create($data);

        static::assertEquals('unit_Desc', $command->getDescription());
        static::assertEquals($startDate, $command->getStartDate());
        static::assertEquals($endDate, $command->getEndDate());
        static::assertEquals('Y', $command->getIsInternal());
    }
}
