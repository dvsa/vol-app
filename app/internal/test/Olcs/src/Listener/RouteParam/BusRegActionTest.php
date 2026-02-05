<?php

declare(strict_types=1);

namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision;
use Hamcrest\Type\IsString;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\BusRegAction;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;

class BusRegActionTest extends MockeryTestCase
{
    /** @var  BusRegAction */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new BusRegAction();

        parent::setUp();
    }

    public function setupMockBusReg(mixed $id, mixed $data): void
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

    public function testAttach(): void
    {
        /** @var EventManagerInterface|m\Mock $mockEventManager */
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->expects('attach')
            ->with(
                RouteParams::EVENT_PARAM . 'busRegId',
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onBusRegAction';
                }),
                1
            );

        $this->sut->attach($mockEventManager);
    }

    public function testOnBusRegAction(): void
    {
        $id = 69;

        $backendResponseKeysByButtonId = [
            'bus-registration-quick-actions-create-cancellation' => 'canCreateCancellation',
            'bus-registration-quick-actions-create-variation' => 'canCreateVariation',
            'bus-registration-quick-actions-print-reg-letter' => 'canPrint',
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

        $buttonMocks['bus-registration-decisions-grant']
            ->shouldReceive('setClass')
            ->once()
            ->with(new IsString());

        $routeParam = new RouteParam();
        $routeParam->setValue($id);

        $event = new Event(null, $routeParam);

        $this->setupMockBusReg($id, $busReg);

        $this->sut->onBusRegAction($event);
    }

    public function testInvoke(): void
    {
        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);

        $service = $this->sut->__invoke($mockSl, BusRegAction::class);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $this->sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $this->sut->getQueryService());
    }

    public function testOnBusRegActionNotFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $id = 69;

        $routeParam = new RouteParam();
        $routeParam->setValue($id);

        $event = new Event(null, $routeParam);

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
     *
     * @param $data
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('shouldOpenGrantButtonInModalProvider')]
    public function testShouldOpenGrantButtonInModal(mixed $data, mixed $expected): void
    {
        $method = new \ReflectionMethod($this->sut, 'shouldOpenGrantButtonInModal');

        $this->assertEquals($expected, $method->invoke($this->sut, $data));
    }

    public static function shouldOpenGrantButtonInModalProvider(): array
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
