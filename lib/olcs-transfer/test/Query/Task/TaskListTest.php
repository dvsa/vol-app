<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Task;

use Dvsa\Olcs\Transfer\Query\Task\TaskList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Task\TaskList::class)]
final class TaskListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'assignedToUser' => 'unit_assignedToUser',
            'category' => 'unit_category',
            'taskSubCategory' => 'unit_taskSubCategory',
            'date' => 'unit_date',
            'status' => 'unit_status',
            'urgent' => 'unit_urgent',
            'messaging' => 'unit_messaging',
            'showTasks' => 'unit_showTasks',
            'assignedToTeam' => 'unit_assignedToTeam',
            'transportManager' => 'unit_transportManager',
            'busReg' => 'unit_busReg',
            'case' => 'unit_case',
            'licence' => 'unit_licence',
            'application' => 'unit_application',
            'organisation' => 'unit_organisation',
            'page' => 'unit_page',
            'limit' => 'unit_limit',
            'sort' => 'unit_sort',
            'order' => 'unit_order',
        ];

        $sut = TaskList::create($data);

        $this->assertEquals('unit_assignedToUser', $sut->getAssignedToUser());
        $this->assertEquals('unit_assignedToTeam', $sut->getAssignedToTeam());
        $this->assertEquals('unit_category', $sut->getCategory());
        $this->assertEquals('unit_taskSubCategory', $sut->getTaskSubCategory());
        $this->assertEquals('unit_date', $sut->getDate());
        $this->assertEquals('unit_status', $sut->getStatus());
        $this->assertEquals('unit_urgent', $sut->getUrgent());
        $this->assertEquals('unit_messaging', $sut->getMessaging());
        $this->assertEquals('unit_showTasks', $sut->getShowTasks());
        $this->assertEquals('unit_transportManager', $sut->getTransportManager());
        $this->assertEquals('unit_organisation', $sut->getOrganisation());
        $this->assertEquals('unit_busReg', $sut->getBusReg());
        $this->assertEquals('unit_case', $sut->getCase());
        $this->assertEquals('unit_licence', $sut->getLicence());
        $this->assertEquals('unit_application', $sut->getApplication());
        $this->assertEquals('unit_page', $sut->getPage());
        $this->assertEquals('unit_limit', $sut->getLimit());
        $this->assertEquals('unit_sort', $sut->getSort());
        $this->assertEquals('unit_order', $sut->getOrder());
    }
}
