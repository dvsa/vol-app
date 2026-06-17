<?php

namespace Dvsa\OlcsTest\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Command\CommunityLic\EditSuspension;

/**
 * Edit suspension test
 */
class EditSuspensionTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'communityLicenceId' => 3,
            'version' => 1,
            'id' => 2,
            'status' => 'foo',
            'startDate' => '2016-01-01',
            'endDate' => '2017-01-01',
            'reasons' => ['bar', 'cake']
        ];

        $command = EditSuspension::create($data);

        $this->assertEquals(1, $command->getVersion());
        $this->assertEquals(2, $command->getId());
        $this->assertEquals(3, $command->getCommunityLicenceId());
        $this->assertEquals('foo', $command->getStatus());
        $this->assertEquals('2016-01-01', $command->getStartDate());
        $this->assertEquals('2017-01-01', $command->getEndDate());
        $this->assertEquals(['bar', 'cake'], $command->getReasons());
    }
}
