<?php

/**
 * IrhpApplication Furniture Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace OlcsTest\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\UrlHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\IrhpApplicationFurniture;
use Mockery as m;
use Olcs\Listener\RouteParam\IrhpPermitFurniture;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Zend\View\Model\ViewModel;
use Zend\View\Helper\Url;

class IrhpApplicationFurnitureTest extends TestCase
{
    /**
     * @var IrhpApplicationFurniture
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new IrhpApplicationFurniture();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
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

        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockQuerySender->shouldReceive('send')->andReturn($mockResult);

        if ($irhpApplicationData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($irhpApplicationData);
        }
    }

    public function testOnIrhpPermit()
    {
        $irhpApplicationId = 2;
        $irhpApplication = [
            'id' => $irhpApplicationId,
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
            'irhpPermitType' => [
                'id' => 4,
                'name' => [
                    'description' => 'Annual Bilateral'
                ]
            ],
            'canBeCancelled' => true,
            'canBeSubmitted' => true,
            'hasOutstandingFees' => false,
        ];

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
                m::mock()->shouldReceive('set')->once()->with('licence_irhp_applications')->getMock()
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
                    ->with('Foo ltd - Permit Application - Annual Bilateral')
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->shouldReceive('get')->once()->with('Url')->andReturn(
                m::mock(Url::class)
                    ->shouldReceive('__invoke')
                    ->once()
                    ->with('licence/permits', ['licence' => $irhpApplication['licence']['id']], [], false)
                    ->andReturn('app')
                    ->getMock()
            )
            ->getMock();

        $mockNavigation = m::mock('Zend\Navigation\Navigation');

        $mockSidebarNavigation = m::mock('Zend\Navigation\Navigation');
        $mockSidebarNavigation
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-quick-actions-cancel')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(true)->getMock()
            )
            ->shouldReceive('findOneBy')->once()->with('id', 'irhp-application-decisions-submit')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(true)->getMock()
            );

        $this->sut->setNavigationService($mockNavigation);
        $this->sut->setSidebarNavigationService($mockSidebarNavigation);

        $mockViewHelperManager->shouldReceive('get')
            ->with('Url');

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $event = new RouteParam();
        $event->setValue($irhpApplicationId);

        $this->sut->onIrhpApplicationFurniture($event);
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
