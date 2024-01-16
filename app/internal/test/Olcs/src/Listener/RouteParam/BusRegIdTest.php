<?php

namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\BusRegId;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class BusRegIdTest extends MockeryTestCase
{
    /**
     * @var BusRegId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new BusRegId();

        parent::setUp();
    }

    public function setupMockBusReg($id, $data)
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResult->shouldReceive('getResult')->with()->once()->andReturn($data);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$this->sut, 'onBusRegId'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnBusRegId()
    {
        $id = 69;
        $busReg = [
            'id' => $id,
            'status' => [
                'id' => RefData::BUSREG_STATUS_REGISTERED,
                'description' => 'description',
            ],
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

        $routeParam = new RouteParam();
        $routeParam->setValue($id);
        $routeParam->setTarget(
            m::mock()
            ->shouldReceive('trigger')
            ->once()
            ->with('licence', 101)
            ->getMock()
        );
        $event = new Event(null, $routeParam);

        $this->setupMockBusReg($id, $busReg);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')
            ->once()
            ->with('busReg')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->with($busReg)
                ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Laminas\View\HelperPluginManager')
            ->shouldReceive('get')
            ->once()
            ->with('placeholder')
            ->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigation = m::mock()
            ->shouldReceive('findOneById')
            ->once()
            ->with('licence_bus_short')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->once()
                ->with(false)
                ->getMock()
            )
            ->getMock();

        $this->sut->setNavigationService($mockNavigation);

        $this->sut->onBusRegId($event);
    }

    public function testInvoke()
    {
        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockNavigation = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);

        $service = $this->sut->__invoke($mockSl, BusRegId::class);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $this->sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $this->sut->getQueryService());
        $this->assertSame($mockNavigation, $this->sut->getNavigationService());
    }

    public function testOnBusRegIdNotFound()
    {
        $this->expectException(ResourceNotFoundException::class);

        $id = 69;

        $routeParam = new RouteParam();
        $routeParam->setValue($id);

        $event = new Event(null, $routeParam);

        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);

        $this->sut->onBusRegId($event);
    }
}
