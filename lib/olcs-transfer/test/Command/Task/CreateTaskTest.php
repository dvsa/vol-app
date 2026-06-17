<?php

namespace Dvsa\OlcsTest\Transfer\Command\Task;

use Dvsa\Olcs\Transfer\Command\Task\CreateTask;

/**
 * CreateTask test
 */
class CreateTaskTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'foo' => 'bar',
            'category' => 111,
            'subCategory' => 222,
            'description' => 'Some Task',
            'actionDate' => '2015-01-01',
            'assignedToUser' => 333,
            'assignedToTeam' => 444,
            'isClosed' => true,
            'urgent' => 'Y',
            'messaging' => 'Y',
            'application' => 555,
            'licence' => 666,
            'busReg' => 123,
            'case' => 124,
            'submission' => 765,
            'transportManager' => 125,
            'irfoOrganisation' => 126,
            'irhpApplication' => 107,
            'assignedByUser' => 7,
            'surrender' => 666,
        ];

        $command = CreateTask::create($data);

        $this->assertEquals(111, $command->getCategory());
        $this->assertEquals(222, $command->getSubCategory());
        $this->assertEquals('Some Task', $command->getDescription());
        $this->assertEquals('2015-01-01', $command->getActionDate());
        $this->assertEquals(333, $command->getAssignedToUser());
        $this->assertEquals(444, $command->getAssignedToTeam());
        $this->assertEquals(true, $command->getIsClosed());
        $this->assertEquals('Y', $command->getUrgent());
        $this->assertEquals('Y', $command->getMessaging());
        $this->assertEquals(555, $command->getApplication());
        $this->assertEquals(666, $command->getLicence());
        $this->assertEquals(123, $command->getBusReg());
        $this->assertEquals(124, $command->getCase());
        $this->assertEquals(125, $command->getTransportManager());
        $this->assertEquals(126, $command->getIrfoOrganisation());
        $this->assertEquals(107, $command->getIrhpApplication());
        $this->assertEquals(765, $command->getSubmission());
        $this->assertEquals(7, $command->getAssignedByUser());
        $this->assertEquals(666, $command->getSurrender());

        $this->assertEquals(
            [
                'category' => 111,
                'subCategory' => 222,
                'description' => 'Some Task',
                'actionDate' => '2015-01-01',
                'assignedToUser' => 333,
                'assignedToTeam' => 444,
                'isClosed' => true,
                'urgent' => 'Y',
                'messaging' => 'Y',
                'application' => 555,
                'licence' => 666,
                'busReg' => 123,
                'case' => 124,
                'submission' => 765,
                'transportManager' => 125,
                'irfoOrganisation' => 126,
                'irhpApplication' => 107,
                'assignedByUser' => 7,
                'surrender' => 666,
            ],
            $command->getArrayCopy()
        );
    }
}
