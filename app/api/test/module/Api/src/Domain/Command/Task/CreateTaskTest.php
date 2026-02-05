<?php

declare(strict_types=1);

/**
 * Create Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Task;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;

/**
 * Create Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateTaskTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
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
            'urgent' => true,
            'messaging' => true,
            'application' => 555,
            'licence' => 666,
            'busReg' => 123,
            'case' => 124,
            'submission' => 765,
            'transportManager' => 125,
            'irfoOrganisation' => 126,
            'irhpApplication' => 973,
            'assignedByUser' => 7,
            'surrender' => 112

        ];

        $command = CreateTask::create($data);

        $this->assertEquals(111, $command->getCategory());
        $this->assertEquals(222, $command->getSubCategory());
        $this->assertEquals('Some Task', $command->getDescription());
        $this->assertEquals('2015-01-01', $command->getActionDate());
        $this->assertEquals(333, $command->getAssignedToUser());
        $this->assertEquals(444, $command->getAssignedToTeam());
        $this->assertEquals(true, $command->getIsClosed());
        $this->assertEquals(true, $command->getUrgent());
        $this->assertEquals(555, $command->getApplication());
        $this->assertEquals(666, $command->getLicence());
        $this->assertEquals(123, $command->getBusReg());
        $this->assertEquals(124, $command->getCase());
        $this->assertEquals(125, $command->getTransportManager());
        $this->assertEquals(126, $command->getIrfoOrganisation());
        $this->assertEquals(973, $command->getIrhpApplication());
        $this->assertEquals(765, $command->getSubmission());
        $this->assertEquals(7, $command->getAssignedByUser());
        $this->assertEquals(112, $command->getSurrender());

        $this->assertEquals(
            [
                'category' => 111,
                'subCategory' => 222,
                'description' => 'Some Task',
                'actionDate' => '2015-01-01',
                'assignedToUser' => 333,
                'assignedToTeam' => 444,
                'isClosed' => true,
                'urgent' => true,
                'messaging' => true,
                'application' => 555,
                'licence' => 666,
                'busReg' => 123,
                'case' => 124,
                'submission' => 765,
                'transportManager' => 125,
                'irfoOrganisation' => 126,
                'irhpApplication' => 973,
                'assignedByUser' => 7,
                'surrender' => 112
            ],
            $command->getArrayCopy()
        );
    }
}
