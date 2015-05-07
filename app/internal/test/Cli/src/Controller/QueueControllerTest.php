<?php

/**
 * Queue Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CliTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Cli\Controller\QueueController;
use OlcsTest\Bootstrap;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * Queue Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueueControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    protected $console;

    public function setUp()
    {
        $this->request = m::mock('Zend\Console\Request');

        $this->routeMatch = new RouteMatch([]);
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);
        $this->sm = Bootstrap::getServiceManager();
        $this->console = m::mock('Zend\Console\Adapter\AdapterInterface');

        $this->sut = new QueueController();
        $this->sut->setEvent($this->event);
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setConsole($this->console);
    }

    public function testIndexActionEmptyQueue()
    {
        // Mocks
        $mockConfig = [
            'queue' => [
                'runFor' => 1
            ]
        ];
        $mockQueue = m::mock();
        $this->sm->setService('Config', $mockConfig);
        $this->sm->setService('Queue', $mockQueue);

        // Expectations
        $this->request->shouldReceive('getParam')
            ->with('type')
            ->andReturn('foo');

        $mockQueue->shouldReceive('processNextItem')
            ->with('foo')
            ->andReturn(null);

        $this->console->shouldReceive('writeLine')
            ->once()
            ->with('No items queued, waiting for items');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }

    public function testIndexAction()
    {
        // Mocks
        $mockConfig = [
            'queue' => [
                'runFor' => 1
            ]
        ];
        $mockQueue = m::mock();
        $this->sm->setService('Config', $mockConfig);
        $this->sm->setService('Queue', $mockQueue);

        // Expectations
        $this->request->shouldReceive('getParam')
            ->with('type')
            ->andReturn('foo');

        $mockQueue->shouldReceive('processNextItem')
            ->with('foo')
            ->andReturn('Some output');

        $this->console->shouldReceive('writeLine')
            ->atLeast(100)
            ->with('Some output');

        // Assertions
        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }
}
