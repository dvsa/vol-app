<?php

namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\BusRegId;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class BusRegIdTest
 * @package OlcsTest\Listener\RouteParam
 */
class BusRegIdTest extends MockeryTestCase
{
    public function setUp()
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
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
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

        $event = new RouteParam();
        $event->setValue($id);
        $event->setTarget(
            m::mock()
            ->shouldReceive('trigger')
            ->once()
            ->with('licence', 101)
            ->getMock()
        );

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
            ->shouldReceive('getContainer')
            ->once()
            ->with('status')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('append')
                ->once()
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('append')
                ->once()
                ->with('org name, Variation 3')
                ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')
            ->once()
            ->with('headTitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('prepend')
                ->once()
                ->with('reg no')
                ->getMock()
            )
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

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockNavigation = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $this->sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $this->sut->getQueryService());
        $this->assertSame($mockNavigation, $this->sut->getNavigationService());
    }

    /**
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testOnBusRegIdNotFound()
    {
        $id = 69;

        $event = new RouteParam();
        $event->setValue($id);

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

    /**
     * @dataProvider getStatusArrayProvider
     */
    public function testGetStatusArray($statusKey, $statusString, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'getStatusArray');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $statusKey, $statusString));
    }

    public function getStatusArrayProvider()
    {
        return [
            [
                RefData::BUSREG_STATUS_ADMIN,
                'value',
                [
                    'colour' => 'grey',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_REGISTERED,
                'value',
                [
                    'colour' => 'green',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_REFUSED,
                'value',
                [
                    'colour' => 'grey',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_CANCELLATION,
                'value',
                [
                    'colour' => 'orange',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_WITHDRAWN,
                'value',
                [
                    'colour' => 'grey',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_VARIATION,
                'value',
                [
                    'colour' => 'orange',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_CNS,
                'value',
                [
                    'colour' => 'grey',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_CANCELLED,
                'value',
                [
                    'colour' => 'grey',
                    'value' => 'value',
                ],
            ],
            [
                RefData::BUSREG_STATUS_NEW,
                'value',
                [
                    'colour' => 'orange',
                    'value' => 'value',
                ],
            ],
        ];
    }
}
