<?php

/**
 * IrhpPermitApplication Furniture Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\UrlHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Data\Mapper\IrhpPermitApplication;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\IrhpPermitFurniture;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Zend\View\Model\ViewModel;
use Zend\View\Helper\Url;

class IrhpPermitFurnitureTest extends TestCase
{
    /**
     * @var IrhpPermitFurniture
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new IrhpPermitFurniture();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'permitid', [$this->sut, 'onIrhpPermitFurniture'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function onIrhpPermitSetup($irhpPermitData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResult = m::mock();

        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockQuerySender->shouldReceive('send')->andReturn($mockResult);

        if ($irhpPermitData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($irhpPermitData);
        }
    }

    public function testOnIrhpPermit()
    {
        $irhpPermitId = 2;
        $irhpPermit = [
            'id' => $irhpPermitId,
            'permitsRequired' => '12',
            'status' => [
                'id' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED
            ],
            'licence' => [
                'id' => 7,
                'licNo' => 'AB1234567',
                'organisation' => [
                    'name' => 'Foo ltd'
                ],
            ],
            'canBeCancelled' => true,
            'canBeSubmitted' => true,
            'hasOutstandingFees' => false,
            'canBeWithdrawn' => false,
            'isAwaitingFee' => false,
            'canBeDeclined' => false
        ];

        $mockUrl = m::mock(UrlHelperService::class);
        $mockUrl->shouldReceive('__invoke');

        $this->onIrhpPermitSetup($irhpPermit);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()
            ->with('status')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->with('licence_irhp_permits')->getMock()
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
                            $this->assertEquals('sections/irhp-permit/partials/right', $view->getTemplate());
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
                    ->with('Foo ltd, Permit Application')
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->shouldReceive('get')->once()->with('Url')->andReturn(
                m::mock(Url::class)
                    ->shouldReceive('__invoke')
                    ->once()
                    ->with('licence/permits', ['licence' => $irhpPermit['licence']['id']], [], false)
                    ->andReturn('app')
                    ->getMock()
            )
            ->getMock();

        $mockNavigation = m::mock()
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp_permits')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(true)->getMock()
            )->getMock();

        $mockSidebarNavigation = m::mock();
        $mockSidebarNavigation
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-permit-quick-actions-cancel')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(true)->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-permit-decisions-submit')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(true)->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-permit-decisions-accept')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-permit-decisions-decline')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-permit-decisions-withdraw')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )->getMock();

        $this->sut->setNavigationService($mockNavigation);
        $this->sut->setSidebarNavigationService($mockSidebarNavigation);

        $mockViewHelperManager->shouldReceive('get')
            ->with('Url');

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $event = new RouteParam();
        $event->setValue($irhpPermitId);

        $this->sut->onIrhpPermitFurniture($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockNavigation = m::mock();
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);
        $mockSidebar = m::mock();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);

        $sut = new IrhpPermitFurniture();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $sut->getQuerySender());
        $this->assertSame($mockCommandSender, $sut->getCommandSender());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
    }
}
