<?php

/**
 * ContinuationChecklistReminderControllerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\ContinuationChecklistReminderController;
use OlcsTest\Bootstrap;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * ContinuationChecklistReminderControllerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationChecklistReminderControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    protected $pm;

    public function setUp()
    {
        $this->request = m::mock('\Zend\Http\Request');

        $this->routeMatch = new RouteMatch([]);
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);
        $this->sm = Bootstrap::getServiceManager();
        $this->pm = m::mock('\Zend\Mvc\Controller\PluginManager')->makePartial();

        $this->sut = new ContinuationChecklistReminderController();
        $this->sut->setEvent($this->event);
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setPluginManager($this->pm);
    }

    public function testIndexAction()
    {
        // Mocks
        $mockDateHelper = m::mock();
        $this->sm->setService('Helper\Date', $mockDateHelper);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();
        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);
        $mockContinuationEntityService = m::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);
        $mockTable = m::mock();
        $mockScript = m::mock();
        $this->sm->setService('Script', $mockScript);

        $mockDateHelper->shouldReceive('getDate')->with('Y-m')->andReturn('2015-05');

        $this->request->shouldReceive('getQuery')->andReturn([]);

        $mockFormHelper->shouldReceive('createForm')->with('ChecklistReminderFilter', false)->once()
            ->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with(['filters' => ['date' => ['month' => '05', 'year' => '2015']]])
            ->andReturnSelf();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(false);

        $mockContinuationEntityService->shouldReceive('getChecklistReminderList')->with('05', '2015')->once()
            ->andReturn(['Count' => 32, 'Results' => ['DATA']]);

        $mockTableBuilder->shouldReceive('prepareTable')->with('admin-continuations-checklist', ['DATA'])->once()
            ->andReturn($mockTable);
        $mockTable->shouldReceive('setVariable')->with('title', 'May 2015: 32 licence(s)');
        $mockScript->shouldReceive('loadFiles')->with(['forms/filter', 'table-actions'])->once();

        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);
        $mockVhm->shouldReceive('get')->with('placeholder')->andReturn(
            m::mock()->shouldReceive('getContainer')->once()->with('navigationId')->andReturn(
                m::mock()->shouldReceive('set')->once()->with('admin-dashboard/continuations')->getMock()
            )
            ->getMock()
        );

        $this->request->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }

    public function testIndexActionFiltered()
    {
        // Mocks
        $mockDateHelper = m::mock();
        $this->sm->setService('Helper\Date', $mockDateHelper);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();
        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);
        $mockContinuationEntityService = m::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);
        $mockTable = m::mock();
        $mockScript = m::mock();
        $this->sm->setService('Script', $mockScript);

        $mockDateHelper->shouldReceive('getDate')->with('Y-m')->andReturn('2015-05');

        $this->request->shouldReceive('getQuery')->andReturn([]);

        $mockFormHelper->shouldReceive('createForm')->with('ChecklistReminderFilter', false)->once()
            ->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with(['filters' => ['date' => ['month' => '05', 'year' => '2015']]])
            ->andReturnSelf();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn(['filters' => ['date' => '2012-12']]);

        $mockContinuationEntityService->shouldReceive('getChecklistReminderList')->with('12', '2012')->once()
            ->andReturn(['Count' => 32, 'Results' => ['DATA']]);

        $mockTableBuilder->shouldReceive('prepareTable')->with('admin-continuations-checklist', ['DATA'])->once()
            ->andReturn($mockTable);
        $mockTable->shouldReceive('setVariable')->with('title', 'Dec 2012: 32 licence(s)');
        $mockScript->shouldReceive('loadFiles')->with(['forms/filter', 'table-actions'])->once();

        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);
        $mockVhm->shouldReceive('get')->with('placeholder')->andReturn(
            m::mock()->shouldReceive('getContainer')->once()->with('navigationId')->andReturn(
                m::mock()->shouldReceive('set')->once()->with('admin-dashboard/continuations')->getMock()
            )
            ->getMock()
        );

        $this->request->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }
}
