<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\System\InfoMessage;

use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Create as CreateCommand;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\System\InfoMessage\Create::class)]
final class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $startDate = new \DateTime()->setTime(0, 0, 0);
        $endDate = new \DateTime()->setTime(23, 59, 59);

        $data = [
            'description' => 'unit_Desc',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isInternal' => 'Y',
        ];
        $command = CreateCommand::create($data);

        $this->assertEquals('unit_Desc', $command->getDescription());
        $this->assertEquals($startDate, $command->getStartDate());
        $this->assertEquals($endDate, $command->getEndDate());
        $this->assertEquals('Y', $command->getIsInternal());
    }
}
