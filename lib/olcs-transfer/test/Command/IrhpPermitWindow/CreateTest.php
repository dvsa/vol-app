<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpPermitWindow;

use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Create;

/**
 * Create test
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'startDate' => '2020-01-01',
            'endDate' => '2020-02-02',
            'irhpPermitStock' => '2',
            'daysForPayment' => '14'
        ];

        $command = Create::create($data);

        $this->assertEquals('2020-01-01', $command->getStartDate());
        $this->assertEquals('2020-02-02', $command->getEndDate());
        $this->assertEquals('2', $command->getIrhpPermitStock());
        $this->assertEquals('14', $command->getDaysForPayment());
    }
}
