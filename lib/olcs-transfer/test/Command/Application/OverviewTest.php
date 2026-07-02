<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\Overview;

/**
 * Overview test
 */
class OverviewTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 222,
            'leadTcArea' => 'B',
            'receivedDate' => '2015-06-10',
            'targetCompletionDate' => '2016-01-02',
            'overrideOppositionDate' => 'N',
            'applicationReferredToPi' => 'N',
            'tracking' => [
                'id' => 333,
                'version' => 444,
                'addressesStatus' => 2,
            ],
        ];

        $command = Overview::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getVersion());
        $this->assertEquals('B', $command->getLeadTcArea());
        $this->assertEquals('2015-06-10', $command->getReceivedDate());
        $this->assertEquals('2016-01-02', $command->getTargetCompletionDate());
        $this->assertEquals('N', $command->getOverrideOppositionDate());
        $this->assertEquals('N', $command->getApplicationReferredToPi());
        $this->assertEquals(
            [
                'id' => 333,
                'version' => 444,
                'addressesStatus' => 2,
            ],
            $command->getTracking()
        );
    }
}
