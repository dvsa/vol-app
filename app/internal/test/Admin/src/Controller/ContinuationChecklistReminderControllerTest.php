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
        $this->markTestSkipped();

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

        // crud with invalid action
        $this->request->shouldReceive('isPost')->with()->once()->andReturn(true);
        $this->request->shouldReceive('getPost')->with()->once()->andReturn([]);

        $mockDateHelper->shouldReceive('getDate')->with('Y-m')->andReturn('2015-05');

        $this->request->shouldReceive('getQuery')->andReturn([]);

        $mockFormHelper->shouldReceive('createForm')->with('ChecklistReminderFilter', false)->once()
            ->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with(['filters' => ['date' => ['month' => '05', 'year' => '2015']]])
            ->andReturnSelf();
        $mockFormHelper->shouldReceive('restoreFormState')->with($mockForm)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(false);

        $mockContinuationEntityService->shouldReceive('getChecklistReminderList')->with('05', '2015')->once()
            ->andReturn(['Count' => 32, 'Results' => ['DATA']]);

        $mockTableBuilder->shouldReceive('prepareTable')->with('admin-continuations-checklist', ['DATA'])->once()
            ->andReturn($mockTable);
        $mockTable->shouldReceive('setVariable')->with('title', 'May 2015: 32 licence(s)');
        $mockScript->shouldReceive('loadFiles')->with(['forms/filter', 'forms/crud-table-handler'])->once();

        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);
        $mockVhm->shouldReceive('get')->with('placeholder')->andReturn(
            m::mock()->shouldReceive('getContainer')->once()->with('navigationId')->andReturn(
                m::mock()->shouldReceive('set')->once()->with('admin-dashboard/continuations')->getMock()
            )
            ->getMock()
            ->shouldReceive('getContainer')->once()->with('tableFilters')->andReturn(
                m::mock()->shouldReceive('set')->once()->with($mockForm)->getMock()
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

        $this->request->shouldReceive('isPost')->with()->once()->andReturn(false);

        $mockDateHelper->shouldReceive('getDate')->with('Y-m')->andReturn('2015-05');

        $this->request->shouldReceive('getQuery')->andReturn(['date' => ['month' => '03', 'year' => '2019']]);

        $mockFormHelper->shouldReceive('createForm')->with('ChecklistReminderFilter', false)->once()
            ->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with(['filters' => ['date' => ['month' => '03', 'year' => '2019']]])
            ->andReturnSelf();
        $mockFormHelper->shouldReceive('saveFormState')
            ->with($mockForm, ['filters' => ['date' => ['month' => '03', 'year' => '2019']]])->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn(['filters' => ['date' => '2012-12']]);

        $mockContinuationEntityService->shouldReceive('getChecklistReminderList')->with('12', '2012')->once()
            ->andReturn(['Count' => 32, 'Results' => ['DATA']]);

        $mockTableBuilder->shouldReceive('prepareTable')->with('admin-continuations-checklist', ['DATA'])->once()
            ->andReturn($mockTable);
        $mockTable->shouldReceive('setVariable')->with('title', 'Dec 2012: 32 licence(s)');
        $mockScript->shouldReceive('loadFiles')->with(['forms/filter', 'forms/crud-table-handler'])->once();

        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);
        $mockVhm->shouldReceive('get')->with('placeholder')->andReturn(
            m::mock()->shouldReceive('getContainer')->once()->with('navigationId')->andReturn(
                m::mock()->shouldReceive('set')->once()->with('admin-dashboard/continuations')->getMock()
            )
            ->getMock()
            ->shouldReceive('getContainer')->once()->with('tableFilters')->andReturn(
                m::mock()->shouldReceive('set')->once()->with($mockForm)->getMock()
            )
            ->getMock()
        );

        $this->request->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }

    public function testIndexActionGenerateLetters()
    {
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        $postData = [
            'action' => 'Generate-letters',
            'id' => [12, 13],
        ];

        $this->request->shouldReceive('isPost')->with()->once()->andReturn(true);
        $this->request->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $mockRedirect->shouldReceive('toRoute')
            ->with(null, ['action' => 'generate-letters', 'child_id' => '12,13'], [], true)->once();

        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }

    public function testGenerateLettersActionSuccess()
    {
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        $mockBsm = m::mock();
        $this->sm->setService('BusinessServiceManager', $mockBsm);

        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockGenerateLetters = m::mock();

        $this->routeMatch->setParam('child_id', '12,13');

        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, ['action' => null, 'child_id' => null], [], true)->once();

        $mockBsm->shouldReceive('get')->with('ContinuationChecklistReminderQueueLetters')->once()
            ->andReturn($mockGenerateLetters);

        $response = new \Common\BusinessService\Response(\Common\BusinessService\Response::TYPE_SUCCESS);

        $mockGenerateLetters->shouldReceive('process')->with(['continuationDetailIds' => [12, 13]])->once()
            ->andReturn($response);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')->once();

        $this->routeMatch->setParam('action', 'generate-letters');
        $this->sut->dispatch($this->request);

    }

    public function testGenerateLettersActionFailed()
    {
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        $mockBsm = m::mock();
        $this->sm->setService('BusinessServiceManager', $mockBsm);

        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockGenerateLetters = m::mock();

        $this->routeMatch->setParam('child_id', '12,13');

        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, ['action' => null, 'child_id' => null], [], true)->once();

        $mockBsm->shouldReceive('get')->with('ContinuationChecklistReminderQueueLetters')->once()
            ->andReturn($mockGenerateLetters);

        $response = new \Common\BusinessService\Response(\Common\BusinessService\Response::TYPE_FAILED);

        $mockGenerateLetters->shouldReceive('process')->with(['continuationDetailIds' => [12, 13]])->once()
            ->andReturn($response);

        $mockFlashMessenger->shouldReceive('addErrorMessage')->once();

        $this->routeMatch->setParam('action', 'generate-letters');
        $this->sut->dispatch($this->request);
    }

    public function testExportAction()
    {
        $mockResponse = m::mock('\Zend\Http\Response');
        $mockForm = m::mock();
        $mockTable = m::mock();
        $mockResponseHelper = m::mock();
        $this->sm->setService('Helper\Response', $mockResponseHelper);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockContinuationEntityService = m::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);
        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);

        $this->routeMatch->setParam('child_id', '11,12');
        $this->request->shouldReceive('getQuery')->andReturn([]);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn(['filters' => ['date' => '2012-12']]);

        $mockFormHelper->shouldReceive('createForm')->with('ChecklistReminderFilter', false)->once()
            ->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with(['filters' => ['date' => ['month' => '1', 'year' => '2000']]])
            ->andReturnSelf();
        $mockFormHelper->shouldReceive('restoreFormState')->with($mockForm)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);

        $mockContinuationEntityService->shouldReceive('getChecklistReminderList')->with('12', '2012')->once()
            ->andReturn(['Count' => 3, 'Results' => [['id' => 10], ['id' => 11], ['id' => 12]]]);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('admin-continuations-checklist', [['id' => 11], ['id' => 12]])
            ->once()->andReturn($mockTable);

        $mockResponseHelper->shouldReceive('tableToCsv')->with($mockResponse, $mockTable, 'Checklist reminder list')
            ->once();

        $this->routeMatch->setParam('action', 'export');
        $this->sut->dispatch($this->request, $mockResponse);
    }
}
