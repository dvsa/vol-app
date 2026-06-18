<?php

namespace Dvsa\OlcsTest\Transfer\Query\Task;

use Dvsa\Olcs\Transfer\Query\Task\TaskList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Task\TaskList
 */
class TaskListTest extends MockeryTestCase
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

        static::assertEquals('unit_assignedToUser', $sut->getAssignedToUser());
        static::assertEquals('unit_assignedToTeam', $sut->getAssignedToTeam());
        static::assertEquals('unit_category', $sut->getCategory());
        static::assertEquals('unit_taskSubCategory', $sut->getTaskSubCategory());
        static::assertEquals('unit_date', $sut->getDate());
        static::assertEquals('unit_status', $sut->getStatus());
        static::assertEquals('unit_urgent', $sut->getUrgent());
        static::assertEquals('unit_messaging', $sut->getMessaging());
        static::assertEquals('unit_showTasks', $sut->getShowTasks());
        static::assertEquals('unit_transportManager', $sut->getTransportManager());
        static::assertEquals('unit_organisation', $sut->getOrganisation());
        static::assertEquals('unit_busReg', $sut->getBusReg());
        static::assertEquals('unit_case', $sut->getCase());
        static::assertEquals('unit_licence', $sut->getLicence());
        static::assertEquals('unit_application', $sut->getApplication());
        static::assertEquals('unit_page', $sut->getPage());
        static::assertEquals('unit_limit', $sut->getLimit());
        static::assertEquals('unit_sort', $sut->getSort());
        static::assertEquals('unit_order', $sut->getOrder());
    }
}
