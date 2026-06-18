<?php

declare(strict_types=1);

namespace CommonTest\Controller;

use Common\Controller\Dispatcher;
use CommonTest\Common\Controller\Traits\Stubs\ControllerDelegateStub;
use Laminas\Mvc\Application;
use Laminas\Mvc\Controller\Plugin\CreateHttpNotFoundModel;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Http\Request;
use Laminas\Http\Response;

class DispatcherTest extends MockeryTestCase
{
    /**
     * @var \CommonTest\Common\Controller\Traits\Stubs\ControllerDelegateStub
     */
    public $delegate;
    public $request;
    public $routeMatch;
    public $response;
    public $pluginManager;
    public $mvcEvent;
    public $dispatcher;
    #[\Override]
    protected function setUp(): void
    {
        $this->delegate = new ControllerDelegateStub();
        $this->request = m::mock(Request::class);
        $this->routeMatch = m::mock(RouteMatch::class);
        $this->response = m::mock(Response::class);
        $this->pluginManager = m::mock(PluginManager::class)->makePartial();

        $this->mvcEvent = m::mock(MvcEvent::class);
        $this->mvcEvent->expects('getRequest')->withNoArgs()->andReturn($this->request);

        $this->dispatcher = new Dispatcher($this->delegate);
        $this->dispatcher->setEvent($this->mvcEvent);
        $this->dispatcher->setPluginManager($this->pluginManager);
    }

    public function testCallAction(): void
    {
        $this->mvcEvent->expects('getResponse')->withNoArgs()->andReturn($this->response);
        $this->mvcEvent->expects('getRouteMatch')->withNoArgs()->andReturn($this->routeMatch);
        $this->routeMatch->expects('getParam')->with('action')->andReturn('index');

        $this->assertEquals('return value', $this->dispatcher->callAction());
    }

    public function testCallActionNotCallable(): void
    {
        $this->routeMatch->expects('getParam')->with('action')->andReturn('private');
        $this->routeMatch->expects('setParam')->with('action', 'not-found');
        $this->mvcEvent->expects('setError')->with(Application::ERROR_CONTROLLER_CANNOT_DISPATCH);
        $this->mvcEvent->expects('getResponse')->withNoArgs()->twice()->andReturn($this->response);
        $this->mvcEvent->expects('getRouteMatch')->withNoArgs()->twice()->andReturn($this->routeMatch);

        $viewModel = new ViewModel(['content' => 'Page not found']);

        $notFoundModel = m::mock(CreateHttpNotFoundModel::class);
        $notFoundModel->expects('__invoke')->with($this->response)->andReturn($viewModel);
        $this->pluginManager->expects('get')->with('createHttpNotFoundModel', null)->andReturn($notFoundModel);

        $this->assertEquals($viewModel, $this->dispatcher->callAction());
    }

    public function testCallActionWithNoAction(): void
    {
        $this->routeMatch->expects('getParam')->with('action')->andReturnNull();
        $this->routeMatch->expects('setParam')->with('action', 'not-found');
        $this->mvcEvent->expects('setError')->with(Application::ERROR_CONTROLLER_CANNOT_DISPATCH);
        $this->mvcEvent->expects('getResponse')->withNoArgs()->twice()->andReturn($this->response);
        $this->mvcEvent->expects('getRouteMatch')->withNoArgs()->twice()->andReturn($this->routeMatch);

        $viewModel = new ViewModel(['content' => 'Page not found']);

        $notFoundModel = m::mock(CreateHttpNotFoundModel::class);
        $notFoundModel->expects('__invoke')->with($this->response)->andReturn($viewModel);
        $this->pluginManager->expects('get')->with('createHttpNotFoundModel', null)->andReturn($notFoundModel);

        $this->assertEquals($viewModel, $this->dispatcher->callAction());
    }
}
