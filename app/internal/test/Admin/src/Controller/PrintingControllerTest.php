<?php

/**
 * PrintingControllerTest
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\PrintingController;
use OlcsTest\Bootstrap;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * PrintingControllerTest
 */
class PrintingControllerTest extends MockeryTestCase
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

        $this->sut = new PrintingController();
        $this->sut->setEvent($this->event);
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setPluginManager($this->pm);
    }

    public function testIndexAction()
    {
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        $mockRedirect->shouldReceive('toRoute')->once()
            ->with(
                'admin-dashboard/admin-printing/irfo-stock-control',
                ['action'=>'index'],
                ['code' => '303'],
                true
            );

        $this->routeMatch->setParam('action', 'index');
        $this->sut->dispatch($this->request);
    }
}
