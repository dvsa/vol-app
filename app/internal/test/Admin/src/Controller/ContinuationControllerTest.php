<?php

/**
 * Continuation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\ContinuationController;
use OlcsTest\Bootstrap;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Common\Service\Entity\ContinuationEntityService;
use Common\BusinessService\Response;
use Common\Service\Entity\LicenceEntityService;

/**
 * Continuation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationControllerTest extends MockeryTestCase
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

        $this->sut = new ContinuationController();
        $this->sut->setEvent($this->event);
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setPluginManager($this->pm);
    }

    public function testIndexAction()
    {
        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')->andReturn(false);

        $this->expectRenderIndex();

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $response = $this->sut->dispatch($this->request);

        $this->assertIndexRenderResponse($response, $mockForm);
    }

    public function testIndexActionPostInvalid()
    {
        $postData = [];

        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(false);

        $this->expectRenderIndex();

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $response = $this->sut->dispatch($this->request);

        $this->assertIndexRenderResponse($response, $mockForm);
    }

    public function testIndexActionPostIrfo()
    {
        $postData = [
            'details' => [
                'type' => ContinuationEntityService::TYPE_IRFO
            ]
        ];

        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $this->expectRedirect('toRoute')
            ->with(null, ['action' => 'irfo'])
            ->andReturn('RESPONSE');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->assertEquals('RESPONSE', $this->sut->dispatch($this->request));
    }

    public function testIndexActionPostExistingContinuation()
    {
        $postData = [
            'details' => [
                'type' => ContinuationEntityService::TYPE_OPERATOR,
                'date' => '2015-01',
                'trafficArea' => 'A'
            ]
        ];

        $stubbedContinuation = [
            'id' => 111
        ];

        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockContinuation = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Continuation', $mockContinuation);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockContinuation->shouldReceive('find')
            ->with(
                [
                    'month' => 1,
                    'year' => 2015,
                    'trafficArea' => 'A'
                ]
            )
            ->andReturn($stubbedContinuation);

        $this->expectRedirect('toRoute')
            ->with('admin-dashboard/admin-continuation/detail', ['id' => 111])
            ->andReturn('RESPONSE');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->assertEquals('RESPONSE', $this->sut->dispatch($this->request));
    }

    public function testIndexActionPostNewContinuationSuccess()
    {
        $postData = [
            'details' => [
                'type' => ContinuationEntityService::TYPE_OPERATOR,
                'date' => '2015-01',
                'trafficArea' => 'A'
            ]
        ];

        $stubbedContinuation = null;

        $expectedCriteria = [
            'month' => 1,
            'year' => 2015,
            'trafficArea' => 'A'
        ];

        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockContinuation = m::mock();
        $mockResponse = m::mock();
        $mockContinuationBs = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Admin\Continuation', $mockContinuationBs);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockContinuation->shouldReceive('find')
            ->with(
                [
                    'month' => 1,
                    'year' => 2015,
                    'trafficArea' => 'A'
                ]
            )
            ->andReturn($stubbedContinuation);

        $mockContinuationBs->shouldReceive('process')
            ->with(['data' => $expectedCriteria])
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('getType')
            ->andReturn(Response::TYPE_SUCCESS)
            ->shouldReceive('getData')
            ->andReturn(['id' => 111]);

        $this->expectRedirect('toRoute')
            ->with('admin-dashboard/admin-continuation/detail', ['id' => 111])
            ->andReturn('RESPONSE');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->assertEquals('RESPONSE', $this->sut->dispatch($this->request));
    }

    public function testIndexActionPostNewContinuationNoop()
    {
        $postData = [
            'details' => [
                'type' => ContinuationEntityService::TYPE_OPERATOR,
                'date' => '2015-01',
                'trafficArea' => 'A'
            ]
        ];

        $stubbedContinuation = null;

        $expectedCriteria = [
            'month' => 1,
            'year' => 2015,
            'trafficArea' => 'A'
        ];

        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockContinuation = m::mock();
        $mockResponse = m::mock();
        $mockFlashMessenger = m::mock();
        $mockContinuationBs = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $bsm->setService('Admin\Continuation', $mockContinuationBs);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockContinuation->shouldReceive('find')
            ->with(
                [
                    'month' => 1,
                    'year' => 2015,
                    'trafficArea' => 'A'
                ]
            )
            ->andReturn($stubbedContinuation);

        $mockContinuationBs->shouldReceive('process')
            ->with(['data' => $expectedCriteria])
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('getType')
            ->andReturn(Response::TYPE_NO_OP);

        $mockFlashMessenger->shouldReceive('addCurrentInfoMessage')
            ->with('admin-continuations-no-licences-found');

        $this->expectRenderIndex();

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $response = $this->sut->dispatch($this->request);

        $this->assertIndexRenderResponse($response, $mockForm);
    }

    public function testIndexActionPostNewContinuationFail()
    {
        $postData = [
            'details' => [
                'type' => ContinuationEntityService::TYPE_OPERATOR,
                'date' => '2015-01',
                'trafficArea' => 'A'
            ]
        ];

        $stubbedContinuation = null;

        $expectedCriteria = [
            'month' => 1,
            'year' => 2015,
            'trafficArea' => 'A'
        ];

        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockContinuation = m::mock();
        $mockResponse = m::mock();
        $mockFlashMessenger = m::mock();
        $mockContinuationBs = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $bsm->setService('Admin\Continuation', $mockContinuationBs);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockContinuation->shouldReceive('find')
            ->with(
                [
                    'month' => 1,
                    'year' => 2015,
                    'trafficArea' => 'A'
                ]
            )
            ->andReturn($stubbedContinuation);

        $mockContinuationBs->shouldReceive('process')
            ->with(['data' => $expectedCriteria])
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('getType')
            ->andReturn(Response::TYPE_FAILED)
            ->shouldReceive('getMessage')
            ->andReturn('MSG');

        $mockFlashMessenger->shouldReceive('addCurrentErrorMessage')
            ->with('MSG');

        $this->expectRenderIndex();

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $response = $this->sut->dispatch($this->request);

        $this->assertIndexRenderResponse($response, $mockForm);
    }

    public function testDetailActionGet()
    {
        $this->routeMatch->setParam('id', 111);

        $stubbedHeaderData = [
            'year' => 2015,
            'month' => 4,
            'trafficArea' => [
                'name' => 'Foo'
            ]
        ];

        $expectedFilters = [
            'licenceStatus' => [
                LicenceEntityService::LICENCE_STATUS_VALID,
                LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                LicenceEntityService::LICENCE_STATUS_CURTAILED,
                LicenceEntityService::LICENCE_STATUS_REVOKED,
                LicenceEntityService::LICENCE_STATUS_SURRENDERED,
                LicenceEntityService::LICENCE_STATUS_TERMINATED
            ]
        ];

        $listData = [
            'Count' => 1,
            'Results' => [
                ['foo' => 'bar']
            ]
        ];

        // Mocks
        $mockForm = m::mock();
        $mockTable = m::mock();
        $mockTranslation = m::mock();
        $mockContinuation = m::mock();
        $mockTableBuilder = m::mock();
        $mockFormHelper = m::mock();
        $mockContinuationDetail = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Helper\Translation', $mockTranslation);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('Table', $mockTableBuilder);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationDetail);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $this->request->shouldReceive('isPost')
            ->andReturn(false)
            ->shouldReceive('getQuery')
            ->andReturn(null)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        $mockContinuation->shouldReceive('getHeaderData')
            ->with(111)
            ->andReturn($stubbedHeaderData);

        $mockTranslation->shouldReceive('translateReplace')
            ->with('admin-continuations-list-title', ['Apr 2015', 'Foo'])
            ->andReturn('TITLE');

        $mockFormHelper->shouldReceive('createForm')
            ->with('ContinuationDetailFilter', false)
            ->andReturn($mockForm);

        $mockForm->shouldReceive('setData')
            ->with(['filters' => $expectedFilters])
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->andReturn(false);

        $mockContinuationDetail->shouldReceive('getListData')
            ->with(111, [])
            ->andReturn($listData);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('admin-continuations', $listData)
            ->andReturn($mockTable);

        $mockTable->shouldReceive('setVariable')
            ->with('title', '1 licence(s)');

        $mockScript->shouldReceive('loadFiles')
            ->with(['forms/filter', 'table-actions']);

        $this->expectSetNavigationId('admin-dashboard/continuations', $mockForm);

        // Assertions
        $this->routeMatch->setParam('action', 'detail');
        $response = $this->sut->dispatch($this->request);

        $contentView = $this->assertRenderView($response);

        $this->assertEquals('pages/table', $contentView->getTemplate());
        $this->assertEquals(['table' => $mockTable, 'filterForm' => $mockForm], $contentView->getVariables());
    }

    public function testDetailActionGetWithFilters()
    {
        $this->routeMatch->setParam('id', 111);

        $stubbedHeaderData = [
            'year' => 2015,
            'month' => 4,
            'trafficArea' => [
                'name' => 'Foo'
            ]
        ];

        $expectedFilters = [
            'licenceStatus' => [
                LicenceEntityService::LICENCE_STATUS_VALID,
                LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                LicenceEntityService::LICENCE_STATUS_CURTAILED,
                LicenceEntityService::LICENCE_STATUS_REVOKED,
                LicenceEntityService::LICENCE_STATUS_SURRENDERED,
                LicenceEntityService::LICENCE_STATUS_TERMINATED
            ]
        ];

        $listData = [
            'Count' => 1,
            'Results' => [
                ['foo' => 'bar']
            ]
        ];

        // Mocks
        $mockForm = m::mock();
        $mockTable = m::mock();
        $mockTranslation = m::mock();
        $mockContinuation = m::mock();
        $mockTableBuilder = m::mock();
        $mockFormHelper = m::mock();
        $mockContinuationDetail = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Helper\Translation', $mockTranslation);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('Table', $mockTableBuilder);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationDetail);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $this->request->shouldReceive('isPost')
            ->andReturn(false)
            ->shouldReceive('getQuery')
            ->andReturn(null)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        $mockContinuation->shouldReceive('getHeaderData')
            ->with(111)
            ->andReturn($stubbedHeaderData);

        $mockTranslation->shouldReceive('translateReplace')
            ->with('admin-continuations-list-title', ['Apr 2015', 'Foo'])
            ->andReturn('TITLE');

        $mockFormHelper->shouldReceive('createForm')
            ->with('ContinuationDetailFilter', false)
            ->andReturn($mockForm);

        $mockForm->shouldReceive('setData')
            ->with(['filters' => $expectedFilters])
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn(['filters' => ['foo' => 'bar']]);

        $mockContinuationDetail->shouldReceive('getListData')
            ->with(111, ['foo' => 'bar'])
            ->andReturn($listData);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('admin-continuations', $listData)
            ->andReturn($mockTable);

        $mockTable->shouldReceive('setVariable')
            ->with('title', '1 licence(s)');

        $mockScript->shouldReceive('loadFiles')
            ->with(['forms/filter', 'table-actions']);

        $this->expectSetNavigationId('admin-dashboard/continuations', $mockForm);

        // Assertions
        $this->routeMatch->setParam('action', 'detail');
        $response = $this->sut->dispatch($this->request);

        $contentView = $this->assertRenderView($response);

        $this->assertEquals('pages/table', $contentView->getTemplate());
        $this->assertEquals(['table' => $mockTable, 'filterForm' => $mockForm], $contentView->getVariables());
    }

    public function testDetailActionPostWithoutCrud()
    {
        $this->routeMatch->setParam('id', 111);

        $stubbedHeaderData = [
            'year' => 2015,
            'month' => 4,
            'trafficArea' => [
                'name' => 'Foo'
            ]
        ];

        $expectedFilters = [
            'licenceStatus' => [
                LicenceEntityService::LICENCE_STATUS_VALID,
                LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                LicenceEntityService::LICENCE_STATUS_CURTAILED,
                LicenceEntityService::LICENCE_STATUS_REVOKED,
                LicenceEntityService::LICENCE_STATUS_SURRENDERED,
                LicenceEntityService::LICENCE_STATUS_TERMINATED
            ]
        ];

        $listData = [
            'Count' => 1,
            'Results' => [
                ['foo' => 'bar']
            ]
        ];

        // Mocks
        $mockForm = m::mock();
        $mockTable = m::mock();
        $mockTranslation = m::mock();
        $mockContinuation = m::mock();
        $mockTableBuilder = m::mock();
        $mockFormHelper = m::mock();
        $mockContinuationDetail = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Helper\Translation', $mockTranslation);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('Table', $mockTableBuilder);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationDetail);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getQuery')
            ->andReturn(null)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        $mockContinuation->shouldReceive('getHeaderData')
            ->with(111)
            ->andReturn($stubbedHeaderData);

        $mockTranslation->shouldReceive('translateReplace')
            ->with('admin-continuations-list-title', ['Apr 2015', 'Foo'])
            ->andReturn('TITLE');

        $mockFormHelper->shouldReceive('createForm')
            ->with('ContinuationDetailFilter', false)
            ->andReturn($mockForm);

        $mockForm->shouldReceive('setData')
            ->with(['filters' => $expectedFilters])
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn(['filters' => ['foo' => 'bar']]);

        $mockContinuationDetail->shouldReceive('getListData')
            ->with(111, ['foo' => 'bar'])
            ->andReturn($listData);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('admin-continuations', $listData)
            ->andReturn($mockTable);

        $mockTable->shouldReceive('setVariable')
            ->with('title', '1 licence(s)');

        $mockScript->shouldReceive('loadFiles')
            ->with(['forms/filter', 'table-actions']);

        $this->expectSetNavigationId('admin-dashboard/continuations', $mockForm);

        // Assertions
        $this->routeMatch->setParam('action', 'detail');
        $response = $this->sut->dispatch($this->request);

        $contentView = $this->assertRenderView($response);

        $this->assertEquals('pages/table', $contentView->getTemplate());
        $this->assertEquals(['table' => $mockTable, 'filterForm' => $mockForm], $contentView->getVariables());
    }

    public function testDetailActionPostWithCrud()
    {
        // Expectations
        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn(['action' => 'add']);

        $this->expectRedirect('toRoute')
            ->with(null, ['action' => 'add'], [], true)
            ->andReturn('REDIRECT');

        // Assertions
        $this->routeMatch->setParam('action', 'detail');
        $response = $this->sut->dispatch($this->request);
        $this->assertEquals('REDIRECT', $response);
    }

    /**
     * Common expections when redirecting
     */
    protected function expectRedirect($method)
    {
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        return $mockRedirect->shouldReceive($method);
    }

    /**
     * Common assertions when calling renderView
     */
    protected function assertRenderView($response, $ajax = false)
    {
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $response);
        $this->assertEquals(($ajax ? 'layout/ajax' : 'layout/base'), $response->getTemplate());

        $header = $response->getChildrenByCaptureTo('header');
        $content = $response->getChildrenByCaptureTo('content');

        $this->assertCount(1, $header);
        $this->assertCount(2, $content);

        $this->assertEquals('partials/header', $header[0]->getTemplate());
        $this->assertEquals('layout/admin-layout', $content[1]->getTemplate());

        $this->assertSame($content[0], $content[1]->getChildren()[0]);

        return $content[0];
    }

    /**
     * Common expectations when setting a navigation id
     */
    protected function expectSetNavigationId($id, $mockForm = null)
    {
        // Mocks
        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);

        $mockPlaceholder = m::mock();

        // Expectations
        $mockVhm->shouldReceive('get')
            ->with('placeholder')
            ->andReturn($mockPlaceholder);

        $mockPlaceholder->shouldReceive('getContainer')
            ->once()
            ->with('navigationId')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->with($id)
                ->getMock()
            )
            ->getMock();

        if ($mockForm) {
            $mockPlaceholder->shouldReceive('getContainer')
                ->once()
                ->with('tableFilters')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($mockForm)
                    ->getMock()
                );
        }
    }

    /**
     * Common assertions when rending the index page
     */
    protected function assertIndexRenderResponse($response, $mockForm)
    {
        $contentView = $this->assertRenderView($response);

        $this->assertEquals('admin-generate-continuations-title', $response->getVariable('pageTitle'));
        $this->assertEquals('pages/form', $contentView->getTemplate());
        $this->assertSame($mockForm, $contentView->getVariable('form'));
    }

    /**
     * Common expectations when rending the index page
     */
    protected function expectRenderIndex()
    {
        $mockScript = m::mock();
        $this->sm->setService('Script', $mockScript);

        $this->request->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        $mockScript->shouldReceive('loadFile')
            ->once()
            ->with('continuations');

        $this->expectSetNavigationId('admin-dashboard/continuations');
    }
}
