<?php

namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\BusRegAction;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class BusRegActionTest
 * @package OlcsTest\Listener\RouteParam
 */
class BusRegActionTest extends MockeryTestCase
{
    /** @var  BusRegAction */
    private $sut;

    public function setUp()
    {
        $this->sut = new BusRegAction();

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
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$this->sut, 'onBusRegAction'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnBusRegAction()
    {
        $id = 69;
        $busReg = [
            'id' => $id,
            'status' => [
                'id' => RefData::BUSREG_STATUS_REGISTERED
            ],
            'isLatestVariation' => true,
            'isGrantable' => true,
            'isShortNotice' => 'Y',
            'shortNoticeRefused' => 'Y',
            'isFromEbsr' => true,
        ];

        $event = new RouteParam();
        $event->setValue($id);

        $this->setupMockBusReg($id, $busReg);

        $mockSidebar = m::mock()
            ->shouldReceive('findById')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->times(11)
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setClass')
                    ->once()
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->sut->setSidebarNavigationService($mockSidebar);

        $this->sut->onBusRegAction($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $this->sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $this->sut->getQueryService());
    }

    /**
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testOnBusRegActionNotFound()
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

        $this->sut->onBusRegAction($event);
    }

    /**
     * @dataProvider shouldShowCreateCancellationButtonProvider
     */
    public function testShouldShowCreateCancellationButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowCreateCancellationButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowCreateCancellationButtonProvider()
    {
        return [
            // latest variation - registered
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                true
            ],
            // previous variation - registered
            [
                [
                    'isLatestVariation' => false,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
            // latest variation - new
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowCreateVariationButtonProvider
     */
    public function testShouldShowCreateVariationButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowCreateVariationButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowCreateVariationButtonProvider()
    {
        return [
            // latest variation - registered
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                true
            ],
            // previous variation - registered
            [
                [
                    'isLatestVariation' => false,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
            // latest variation - new
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowRequestNewRouteMapButtonProvider
     */
    public function testShouldShowRequestNewRouteMapButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowRequestNewRouteMapButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowRequestNewRouteMapButtonProvider()
    {
        return [
            // EBSR
            [
                [
                    'isFromEbsr' => true,
                ],
                true
            ],
            // non-EBSR
            [
                [
                    'isFromEbsr' => false,
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowRequestWithdrawnButtonProvider
     */
    public function testShouldShowRequestWithdrawnButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowRequestWithdrawnButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowRequestWithdrawnButtonProvider()
    {
        return [
            // new
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                true
            ],
            // variation
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION
                    ],
                ],
                true
            ],
            // cancellation
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLATION
                    ],
                ],
                true
            ],
            // registered
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowRepublishButtonProvider
     */
    public function testShouldShowRepublishButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowRepublishButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowRepublishButtonProvider()
    {
        return [
            // latest variation - registered
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                true
            ],
            // previous variation - registered
            [
                [
                    'isLatestVariation' => false,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
            // latest variation - cancelled
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLED
                    ],
                ],
                true
            ],
            // latest variation - new
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowAdminCancelButtonProvider
     */
    public function testShouldShowAdminCancelButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowAdminCancelButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowAdminCancelButtonProvider()
    {
        return [
            // latest variation - registered
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                true
            ],
            // previous variation - registered
            [
                [
                    'isLatestVariation' => false,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
            // latest variation - new
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowGrantButtonProvider
     */
    public function testShouldShowGrantButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowGrantButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowGrantButtonProvider()
    {
        return [
            // grantable - new
            [
                [
                    'isGrantable' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                true
            ],
            // non-grantable - new
            [
                [
                    'isGrantable' => false,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                false
            ],
            // grantable - variation
            [
                [
                    'isGrantable' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION
                    ],
                ],
                true
            ],
            // grantable - cancellation
            [
                [
                    'isGrantable' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLATION
                    ],
                ],
                true
            ],
            // grantable - registered
            [
                [
                    'isGrantable' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldOpenGrantButtonInModalProvider
     */
    public function testShouldOpenGrantButtonInModal($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldOpenGrantButtonInModal');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldOpenGrantButtonInModalProvider()
    {
        return [
            // variation
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION
                    ],
                ],
                true
            ],
            // cancellation
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLATION
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowRefuseButtonProvider
     */
    public function testShouldShowRefuseButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowRefuseButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowRefuseButtonProvider()
    {
        return [
            // new
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                true
            ],
            // variation
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION
                    ],
                ],
                true
            ],
            // cancellation
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLATION
                    ],
                ],
                true
            ],
            // registered
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowRefuseByShortNoticeButtonProvider
     */
    public function testShouldShowRefuseByShortNoticeButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowRefuseByShortNoticeButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowRefuseByShortNoticeButtonProvider()
    {
        return [
            // new - short notice - not refused
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                    'isShortNotice' => 'Y',
                    'shortNoticeRefused' => 'N',
                ],
                true
            ],
            // new - non short notice - not refused
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                    'isShortNotice' => 'N',
                    'shortNoticeRefused' => 'N',
                ],
                false
            ],
            // new - short notice - refused
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                    'isShortNotice' => 'Y',
                    'shortNoticeRefused' => 'Y',
                ],
                false
            ],
            // variation - short notice - not refused
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION
                    ],
                    'isShortNotice' => 'Y',
                    'shortNoticeRefused' => 'N',
                ],
                true
            ],
            // cancellation - short notice - not refused
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLATION
                    ],
                    'isShortNotice' => 'Y',
                    'shortNoticeRefused' => 'N',
                ],
                true
            ],
            // registered
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                    'isShortNotice' => 'Y',
                    'shortNoticeRefused' => 'N',
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider shouldShowResetRegistrationButtonProvider
     */
    public function testShouldShowResetRegistrationButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'shouldShowResetRegistrationButton');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function shouldShowResetRegistrationButtonProvider()
    {
        return [
            // latest variation - new
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW
                    ],
                ],
                false
            ],
            // latest variation - variation
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION
                    ],
                ],
                false
            ],
            // latest variation - cancellation
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_CANCELLATION
                    ],
                ],
                false
            ],
            // latest variation - registered
            [
                [
                    'isLatestVariation' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                true
            ],
            // previous variation - new
            [
                [
                    'isLatestVariation' => false,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsVisiblePrintLetterButton
     */
    public function testIsVisiblePrintLetterButton($data, $expected)
    {
        $method = new \ReflectionMethod($this->sut, 'isVisiblePrintLetterButton');
        $method->setAccessible(true);

        static::assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public function dpTestIsVisiblePrintLetterButton()
    {
        return [
            //  REGISTERED
            [
                [
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_REGISTERED
                    ],
                ],
                true
            ],
            // not visible
            [
                [
                    'status' => [
                        'id' => 'UNIT_OTHER',
                    ],
                ],
                false
            ],
        ];
    }
}
