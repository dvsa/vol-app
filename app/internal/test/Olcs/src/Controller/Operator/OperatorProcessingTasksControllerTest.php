<?php

/**
 * Operator tasks controller tests
 */

namespace OlcsTest\Controller\Operator;

use Olcs\Controller\Operator\OperatorProcessingTasksController as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Operator tasks controller tests
 */
class OperatorProcessingTasksControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * Test the index action
     * @group task
     */
    public function testIndexActionWithDefaultParams()
    {
        $organisationId  = 69;
        $filters = ['filter' => 'value'];

        $this->sut->shouldReceive('processTasksActions')->once()->with('organisation')->andReturn(false);

        // mock params
        $mockParams =  m::mock()
            ->shouldReceive('fromRoute')
            ->once()
            ->with('organisation')
            ->andReturn($organisationId)
            ->getMock();
        $this->sut->shouldReceive('params')->andReturn($mockParams);

        $this->sut->shouldReceive('mapTaskFilters')->once()
            ->with(
                [
                    'organisation' => $organisationId,
                    'assignedToTeam' => '',
                    'assignedToUser' => ''
                ]
            )
            ->andReturn($filters);

        // mock table
        $mockTable =  m::mock()
            ->shouldReceive('removeColumn')
            ->once()
            ->with('name')
            ->shouldReceive('removeColumn')
            ->once()
            ->with('link')
            ->getMock();
        $this->sut->shouldReceive('getTaskTable')->once()->with($filters)->andReturn($mockTable);

        // mock form
        $mockForm =  m::mock();

        $this->sut->shouldReceive('getTaskForm')->with($filters)->andReturn($mockForm);

        $this->sut->shouldReceive('setTableFilters')->with($mockForm);

        $this->sut->shouldReceive('loadScripts')
            ->with(['tasks', 'table-actions', 'forms/filter']);

        $this->sut->shouldReceive('renderView')->once()->andReturn('view');

        $result = $this->sut->indexAction();

        $this->assertEquals('view', $result);
    }

    /**
     * Test the index action
     * @group task
     */
    public function testIndexActionWithRedir()
    {
        $this->sut->shouldReceive('processTasksActions')->once()->with('organisation')->andReturn('redir');

        $result = $this->sut->indexAction();

        $this->assertEquals('redir', $result);
    }
}
