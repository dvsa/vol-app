<?php

/**
 * IrfoStockControlControllerTest
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\IrfoStockControlController;
use OlcsTest\Bootstrap;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * IrfoStockControlControllerTest
 */
class IrfoStockControlControllerTest extends MockeryTestCase
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

        $this->sut = new IrfoStockControlController();
        $this->sut->setEvent($this->event);
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setPluginManager($this->pm);
    }

    public function testIndexAction()
    {
        $expectedFilters = [
            'validForYear' => '2019',
            'page' => 1,
            'sort' => 'serialNo',
            'order' => 'ASC',
            'limit' => 25,
            'irfoCountry' => 100,
        ];

        // Mocks
        $mockDateHelper = m::mock();
        $this->sm->setService('Helper\Date', $mockDateHelper);

        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockForm = m::mock();
        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);

        $mockTable = m::mock();
        $mockScript = m::mock();
        $this->sm->setService('Script', $mockScript);

        $mockNavigation = m::mock();
        $this->sm->setService('Navigation', $mockNavigation);

        $mockIrfoCountry = m::mock();
        $this->sm->setService('Olcs\Service\Data\IrfoCountry', $mockIrfoCountry);

        $mockIrfoPermitStock = m::mock();
        $this->sm->setService('Admin\Service\Data\IrfoPermitStock', $mockIrfoPermitStock);

        $this->request->shouldReceive('isPost')->with()->andReturn(false);
        $this->request->shouldReceive('getPost')->with()->andReturn([]);

        $mockNavigation->shouldReceive('findOneBy')
            ->with('id', 'admin-dashboard/admin-printing/irfo-stock-control')
            ->andReturn(
                m::mock()->shouldReceive('setActive')->once()->getMock()
            );

        $mockFormHelper->shouldReceive('createForm')->with('IrfoStockControlFilter')->once()->andReturn($mockForm);
        $mockForm->shouldReceive('hasAttribute')->with('action')->once()->andReturn(true);
        $mockForm->shouldReceive('getFieldsets')->once()->andReturn([]);
        $mockForm->shouldReceive('remove')->with('csrf')->once();
        $mockForm->shouldReceive('setData')->with($expectedFilters)->once()->andReturnSelf();

        $mockDateHelper->shouldReceive('getDate')->with('Y')->andReturn('2015');

        $this->request->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('toArray')->once()->andReturn(['validForYear' => '2019'])->getMock()
        );

        $mockIrfoCountry->shouldReceive('fetchListData')->with()->once()->andReturn([['id' => 100]]);

        $mockIrfoPermitStock->shouldReceive('fetchIrfoPermitStockList')->with($expectedFilters)->once()
            ->andReturn(['DATA']);

        $mockTableBuilder->shouldReceive('buildTable')->with(
            'admin-irfo-stock-control',
            ['DATA'],
            m::type('array'),
            false
        )->once()
        ->andReturn($mockTable);

        $mockScript->shouldReceive('loadFiles')->with(['table-actions'])->once();

        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);
        $mockVhm->shouldReceive('get')->with('placeholder')->andReturn(
            m::mock()->shouldReceive('getContainer')->once()->with('navigationId')->andReturn(
                m::mock()->shouldReceive('set')->once()
                ->with('admin-dashboard/admin-printing/irfo-stock-control')->getMock()
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
}
