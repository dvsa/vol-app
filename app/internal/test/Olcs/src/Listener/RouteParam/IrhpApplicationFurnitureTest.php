<?php

namespace OlcsTest\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\UrlHelperService;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Laminas\View\HelperPluginManager;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\IrhpApplicationFurniture;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Laminas\View\Model\ViewModel;
use Laminas\View\Helper\Url;

class IrhpApplicationFurnitureTest extends TestCase
{
    /**
     * @var IrhpApplicationFurniture
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(IrhpApplicationFurniture::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock(\Laminas\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(
                RouteParams::EVENT_PARAM . 'irhpAppId',
                [$this->sut, 'onIrhpApplicationFurniture'],
                1
            );

        $this->sut->attach($mockEventManager);
    }

    protected function onIrhpPermitSetup($irhpApplicationData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResult = m::mock();

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockQuerySender->shouldReceive('send')->andReturn($mockResult);

        if ($irhpApplicationData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($irhpApplicationData);
        }
    }

    /**
     * @dataProvider dpOnIrhpApplicationFurniture
     */
    public function testOnIrhpApplicationFurniture($data, $expected)
    {
        $irhpApplicationId = 2;
        $irhpAppData = [
            'id' => $irhpApplicationId,
            'status' => [
                'id' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION
            ],
            'licence' => [
                'id' => 7,
                'licNo' => 'AB1234567',
                'organisation' => [
                    'name' => 'Foo ltd'
                ],
            ],
            'irhpPermitType' => [
                'id' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                'name' => [
                    'description' => 'ECMT Short Term'
                ]
            ],
            'businessProcess' => ['id' => RefData::BUSINESS_PROCESS_APGG]
        ];

        $irhpApplication = array_merge($irhpAppData, $data);

        $mockUrl = m::mock(UrlHelperService::class);
        $mockUrl->shouldReceive('__invoke');

        $this->onIrhpPermitSetup($irhpApplication);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()
            ->with('status')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->with('licence_irhp_permits-application')->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('irhpPermit')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('right')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with(m::type(ViewModel::class))
                    ->andReturnUsing(
                        function ($view) {
                            $this->assertEquals('sections/irhp-application/partials/right', $view->getTemplate());
                        }
                    )
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with('Foo ltd - Permit Application - ECMT Short Term')
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class)
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->shouldReceive('get')->once()->with('Url')->andReturn(
                m::mock(Url::class)
                    ->shouldReceive('__invoke')
                    ->once()
                    ->with('licence/irhp-application', ['licence' => $irhpApplication['licence']['id']], [], false)
                    ->andReturn('app')
                    ->getMock()
            )
            ->getMock();

        $this->sut->shouldReceive('getGrantability')->with($irhpApplication)
            ->andReturn(['grantable' => $data['isGrantable']]);

        $mockNavigation = m::mock(\Laminas\Navigation\Navigation::class);

        $mockSidebarNavigation = m::mock(\Laminas\Navigation\Navigation::class);
        $mockSidebarNavigation
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-quick-actions-cancel')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($expected['isCancelVisible'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-quick-actions-terminate')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($expected['isTerminateVisible'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-quick-actions-reset-to-not-yet-submitted-from-cancelled')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($data['canBeResetToNotYetSubmittedFromCancelled'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-submit')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($expected['isSubmitVisible'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-grant')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($data['isGrantable'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-withdraw')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($expected['isWithdrawVisible'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-revive-from-withdrawn')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($data['canBeRevivedFromWithdrawn'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-revive-from-unsuccessful')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($data['canBeRevivedFromUnsuccessful'])->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-reset-to-not-yet-submitted-from-valid')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with($data['canBeResetToNotYetSubmittedFromValid'])->getMock()
            );

        $mockNavigation->shouldReceive('findOneBy')->once()->with('id', 'licence_irhp_applications-pregrant')->andReturn(
            m::mock()->shouldReceive('setVisible')->once()->with(true)->getMock()
        );
        $mockNavigation->shouldReceive('findOneBy')->once()->with('id', 'irhp_permits-permits')->andReturn(
            m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
        );

        $this->sut->setNavigationService($mockNavigation);
        $this->sut->setSidebarNavigationService($mockSidebarNavigation);

        $mockViewHelperManager->shouldReceive('get')
            ->with('Url');

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $routeParam = new RouteParam();
        $routeParam->setValue($irhpApplicationId);

        $event = new Event(null, $routeParam);

        $this->sut->onIrhpApplicationFurniture($event);
    }

    public function dpOnIrhpApplicationFurniture()
    {
        return [
            [
                [
                    'canBeCancelled' => false,
                    'canBeTerminated' => false,
                    'canBeSubmitted' => false,
                    'canBeWithdrawn' => false,
                    'canBeDeclined' => false,
                    'canBeGranted' => false,
                    'canPreGrant' => true,
                    'isGrantable' => false,
                    'canBeRevivedFromWithdrawn' => false,
                    'canBeRevivedFromUnsuccessful' => false,
                    'canBeResetToNotYetSubmittedFromValid' => false,
                    'canBeResetToNotYetSubmittedFromCancelled' => false,
                ],
                [
                    'isCancelVisible' => false,
                    'isTerminateVisible' => false,
                    'isSubmitVisible' => false,
                    'isWithdrawVisible' => false,
                    'isGrantVisible' => false,
                ]
            ],
            [
                [
                    'canBeCancelled' => false,
                    'canBeTerminated' => false,
                    'canBeSubmitted' => false,
                    'canBeWithdrawn' => true,
                    'canBeDeclined' => false,
                    'canBeGranted' => false,
                    'canPreGrant' => true,
                    'isGrantable' => false,
                    'canBeRevivedFromWithdrawn' => false,
                    'canBeRevivedFromUnsuccessful' => false,
                    'canBeResetToNotYetSubmittedFromValid' => true,
                    'canBeResetToNotYetSubmittedFromCancelled' => true,
                ],
                [
                    'isCancelVisible' => false,
                    'isTerminateVisible' => false,
                    'isSubmitVisible' => false,
                    'isWithdrawVisible' => true,
                    'isGrantVisible' => false,
                ]
            ],
            [
                [
                    'canBeCancelled' => false,
                    'canBeTerminated' => false,
                    'canBeSubmitted' => false,
                    'canBeWithdrawn' => false,
                    'canBeDeclined' => true,
                    'canBeGranted' => false,
                    'canPreGrant' => true,
                    'isGrantable' => false,
                    'canBeRevivedFromWithdrawn' => false,
                    'canBeRevivedFromUnsuccessful' => false,
                    'canBeResetToNotYetSubmittedFromValid' => false,
                    'canBeResetToNotYetSubmittedFromCancelled' => false,
                ],
                [
                    'isCancelVisible' => false,
                    'isTerminateVisible' => false,
                    'isSubmitVisible' => false,
                    'isWithdrawVisible' => true,
                    'isGrantVisible' => false,
                ]
            ],
            [
                [
                    'canBeCancelled' => true,
                    'canBeTerminated' => true,
                    'canBeSubmitted' => true,
                    'canBeWithdrawn' => true,
                    'canBeDeclined' => true,
                    'canBeGranted' => true,
                    'canPreGrant' => true,
                    'isGrantable' => true,
                    'canBeRevivedFromWithdrawn' => true,
                    'canBeRevivedFromUnsuccessful' => false,
                    'canBeResetToNotYetSubmittedFromValid' => false,
                    'canBeResetToNotYetSubmittedFromCancelled' => false,
                ],
                [
                    'isCancelVisible' => true,
                    'isTerminateVisible' => true,
                    'isSubmitVisible' => true,
                    'isWithdrawVisible' => true,
                    'isGrantVisible' => true,
                ]
            ],
        ];
    }

    public function testInvoke()
    {
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockNavigation = m::mock(\Laminas\Navigation\Navigation::class);
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);
        $mockSidebar = m::mock(\Laminas\Navigation\Navigation::class);
        $mockApplication = m::mock(\Laminas\Mvc\Application::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);
        $mockSl->shouldReceive('get')->with('navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('Application')->andReturn($mockApplication);

        $sut = new IrhpApplicationFurniture();
        $service = $sut->__invoke($mockSl, IrhpApplicationFurniture::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $sut->getQuerySender());
        $this->assertSame($mockCommandSender, $sut->getCommandSender());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
    }
}
