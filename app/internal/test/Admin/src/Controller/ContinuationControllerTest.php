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

    public function setUp()
    {
        $this->request = m::mock('\Zend\Http\Request');

        $this->routeMatch = new RouteMatch([]);
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new ContinuationController();
        $this->sut->setEvent($this->event);
        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        // Mocks
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockScript = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(false)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false);

        $mockScript->shouldReceive('loadFile')
            ->once()
            ->with('continuations');

        $this->expectSetNavigationId('admin-dashboard/continuations');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $response = $this->sut->dispatch($this->request);

        $contentView = $this->assertRenderView($response);

        $this->assertEquals('admin-generate-continuations-title', $response->getVariable('pageTitle'));
        $this->assertEquals('partials/form', $contentView->getTemplate());
        $this->assertSame($mockForm, $contentView->getVariable('form'));
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
        $mockScript = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenerateContinuation')
            ->andReturn($mockForm);

        $this->request->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockScript->shouldReceive('loadFile')
            ->once()
            ->with('continuations');

        $this->expectSetNavigationId('admin-dashboard/continuations');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $response = $this->sut->dispatch($this->request);

        $contentView = $this->assertRenderView($response);

        $this->assertEquals('admin-generate-continuations-title', $response->getVariable('pageTitle'));
        $this->assertEquals('partials/form', $contentView->getTemplate());
        $this->assertSame($mockForm, $contentView->getVariable('form'));
    }

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

        // Assert that placeholder is actually child of admin-layout
        $this->assertSame($content[0], $content[1]->getChildren()[0]);

        return $content[0];
    }

    protected function expectSetNavigationId($id)
    {
        // Mocks
        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);

        // Expectations
        $mockVhm->shouldReceive('get')
            ->with('placeholder')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContainer')
                ->once()
                ->with('navigationId')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($id)
                    ->getMock()
                )
                ->getMock()
            );
    }
}
