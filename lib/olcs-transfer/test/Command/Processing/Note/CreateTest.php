<?php

namespace Dvsa\OlcsTest\Transfer\Command\Processing\Note;

use PHPUnit\Framework\TestCase as TestCase;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Create;

/**
 * Create test
 */
class CreateTest extends TestCase
{
    public function testStructure()
    {
        $data = [
            'foo' => 'bar',
            'application' => 1,
            'busReg' => 2,
            'case' => 3,
            'licence' => 4,
            'organisation' => 5,
            'transportManager' => 6,
            'irhpApplication' => 7,
            'comment' => 'Some text',
            'priority' => 'Y',
        ];

        $command = Create::create($data);

        $this->assertEquals(1, $command->getApplication());
        $this->assertEquals(2, $command->getBusReg());
        $this->assertEquals(3, $command->getCase());
        $this->assertEquals(4, $command->getLicence());
        $this->assertEquals(5, $command->getOrganisation());
        $this->assertEquals(6, $command->getTransportManager());
        $this->assertEquals(7, $command->getIrhpApplication());
        $this->assertEquals('Some text', $command->getComment());
        $this->assertEquals('Y', $command->getPriority());

        $this->assertEquals(
            [
                'application' => 1,
                'busReg' => 2,
                'case' => 3,
                'licence' => 4,
                'organisation' => 5,
                'transportManager' => 6,
                'irhpApplication' => 7,
                'comment' => 'Some text',
                'priority' => 'Y',
            ],
            $command->getArrayCopy()
        );
    }
}
