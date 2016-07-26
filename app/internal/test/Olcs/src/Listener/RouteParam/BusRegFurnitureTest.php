<?php

/**
 * Bus Reg Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Command\Audit\ReadBusReg;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\BusRegFurniture;
use Olcs\Listener\RouteParams;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Zend\View\Model\ViewModel;

/**
 * Bus Reg Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusRegFurnitureTest extends MockeryTestCase
{
    /**
     * @var BusRegFurniture
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new BusRegFurniture();
    }

    public function setupMockBusReg($data)
    {
        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResult->shouldReceive('getResult')->with()->once()->andReturn($data);

        $mockQuerySender = m::mock(QuerySender::class);
        $mockQuerySender->shouldReceive('send')->once()->andReturn($mockResult);
        $this->sut->setQuerySender($mockQuerySender);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$this->sut, 'onBusRegFurniture'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnBusRegFurniture()
    {
        $id = 69;

        $status = [
            'id' => RefData::BUSREG_STATUS_REGISTERED,
            'description' => 'description',
        ];

        $busReg = [
            'id' => $id,
            'status' => $status,
            'regNo' => 'reg no',
            'routeNo' => 'route no',
            'variationNo' => 3,
            'isShortNotice' => 'N',
            'licence' => [
                'id' => 101,
                'licNo' => '111',
                'organisation' => [
                    'name' => 'org name'
                ]
            ],
        ];

        $event = new RouteParam();
        $event->setValue($id);

        $this->setupMockBusReg($busReg);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')
            ->once()
            ->with('status')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->with($status)
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->with('org name, Variation 3')
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('licence_bus')
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('right')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with(m::type(ViewModel::class))
                    ->andReturnUsing(
                        function ($right) {
                            $this->assertEquals('sections/bus/partials/right', $right->getTemplate());
                        }
                    )
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')
            ->once()
            ->with('placeholder')
            ->andReturn($mockPlaceholder)
            ->shouldReceive('get')
            ->once()
            ->with('Url')
            ->andReturn(
                m::mock()
                ->shouldReceive('__invoke')
                ->once()
                ->with('licence/bus', ['licence' => 101], [], true)
                ->getMock()
            )
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $this->sut->onBusRegFurniture($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $this->sut->getQuerySender());
        $this->assertSame($mockCommandSender, $this->sut->getCommandSender());
    }

    /**
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testOnBusRegFurnitureNotFound()
    {
        $id = 69;

        $event = new RouteParam();
        $event->setValue($id);

        $mockQuerySender = m::mock(QuerySender::class);

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);

        $mockQuerySender ->shouldReceive('send')->once()->andReturn($mockResult);

        $this->sut->setQuerySender($mockQuerySender);

        $this->sut->onBusRegFurniture($event);
    }
}
