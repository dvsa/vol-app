<?php

namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision;
use Hamcrest\Type\IsString;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\BusRegAction;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Zend\EventManager\EventManagerInterface;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\AbstractPage;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BusRegActionTest
 *
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
        $mockQueryService = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function (BusRegDecision $dto) use ($id) {
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
        /** @var EventManagerInterface|m\Mock $mockEventManager */
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$this->sut, 'onBusRegAction'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnBusRegAction()
    {
        $id = 69;

        $backendResponseKeysByButtonId = [
            'bus-registration-quick-actions-create-cancellation' => 'canCreateCancellation',
            'bus-registration-quick-actions-print-reg-letter' => 'canPrintLetter',
            'bus-registration-quick-actions-request-new-route-map' => 'canRequestNewRouteMap',
            'bus-registration-quick-actions-request-withdrawn' => 'canWithdraw',
            'bus-registration-quick-actions-republish' => 'canRepublish',
            'bus-registration-decisions-admin-cancel' => 'canCancelByAdmin',
            'bus-registration-decisions-refuse' => 'canRefuse',
            'bus-registration-decisions-refuse-by-short-notice' => 'canRefuseByShortNotice',
            'bus-registration-decisions-reset-registration' => 'canResetRegistration',
            'bus-registration-decisions-grant' => 'isGrantable',
        ];

        $busReg = [
            'isLatestVariation' => true,
            'status' => [
                'id' => 'test',
            ],
        ];

        /** @var Navigation|m\Mock $mockSidebar */
        $mockSidebar = m::mock(Navigation::class);
        $this->sut->setSidebarNavigationService($mockSidebar);

        /** @var m\Mock[] $buttonMocks */
        $buttonMocks = [];

        foreach ($backendResponseKeysByButtonId as $buttonId => $backendResponseKey) {
            if (!array_key_exists($backendResponseKey, $busReg)) {
                $busReg[$backendResponseKey] = new \stdClass();
            }
            $buttonMock = m::mock(AbstractPage::class)
                ->shouldReceive('setVisible')
                ->once()
                ->with($busReg[$backendResponseKey])
                ->andReturnSelf()
                ->getMock();
            $buttonMocks[$buttonId] = $buttonMock;
            $mockSidebar->shouldReceive('findOneBy')
                ->with('id', $buttonId)
                ->andReturn($buttonMock);
        }

        $mockSidebar->shouldReceive('findOneBy')
            ->with('id', 'bus-registration-quick-actions-create-variation')
            ->andReturn(
                m::mock(AbstractPage::class)
                    ->shouldReceive('setVisible')
                    ->once()
                    ->getMock()
            );

        $buttonMocks['bus-registration-decisions-grant']
            ->shouldReceive('setClass')
            ->once()
            ->with(new IsString());

        $event = new RouteParam();
        $event->setValue($id);

        $this->setupMockBusReg($id, $busReg);

        $this->sut->onBusRegAction($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();

        /** @var ServiceLocatorInterface|m\Mock $mockSl */
        $mockSl = m::mock(ServiceLocatorInterface::class);
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
        $mockQueryService = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function (BusRegDecision $dto) use ($id) {
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
     * @dataProvider shouldOpenGrantButtonInModalProvider
     *
     * @param $data
     * @param $expected
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
}
