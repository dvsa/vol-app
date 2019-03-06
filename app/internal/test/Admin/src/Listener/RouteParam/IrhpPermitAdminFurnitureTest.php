<?php

/**
 * Irhp Permit Admin Furniture Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
namespace AdminTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\UrlHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Admin\Listener\RouteParam\IrhpPermitAdminFurniture;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Zend\Navigation\Navigation;
use Zend\View\Helper\Placeholder;
use Zend\View\HelperPluginManager;

class IrhpPermitAdminFurnitureTest extends TestCase
{
    /**
     * @var IrhpPermitAdminFurniture
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new IrhpPermitAdminFurniture();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'stockId', [$this->sut, 'onIrhpPermitAdminFurniture'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function onIrhpPermitAdminSetup($irhpPermitStockData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResult = m::mock();

        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockQuerySender->shouldReceive('send')->once()->andReturn($mockResult);

        if ($irhpPermitStockData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($irhpPermitStockData);
        }
    }

    public function testOnIrhpPermitQueryError()
    {
        $this->onIrhpPermitAdminSetup(false);
        $event = new RouteParam();
        $event->setValue(32);

        $this->expectException(ResourceNotFoundException::class);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testOnIrhpPermitAdmin()
    {
        $stockId = 1;
        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2018',
            'validTo' => '31-12-2018',
            'initialStock' => 100,
            'irhpPermitType' => [
                'id' => '2',
                'name' => [
                    'description' => 'ECMT'
                ]
            ]
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('Permits')
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with("Type: ECMT Validity: 01/01/2018 to 31/12/2018 Quota: 100")
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $mockViewHelperManager->shouldReceive('get')
            ->with('Url');

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $event = new RouteParam();
        $event->setValue($stockId);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testOnIrhpPermitAdminBilateralId()
    {
        $stockId = 1;
        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2018',
            'validTo' => '31-12-2018',
            'initialStock' => 100,
            'irhpPermitType' => [
                'id' => '4',
                'name' => [
                    'description' => 'Annual Bilateral permits (EU and EEA)'
                ]
            ]
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('Permits')
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with("Type: Annual Bilateral permits (EU and EEA) Validity: 01/01/2018 to 31/12/2018 Quota: 100")
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $mockViewHelperManager->shouldReceive('get')
            ->with('Url');

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $event = new RouteParam();
        $event->setValue($stockId);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testOnIrhpPermitAdminMultilaterallId()
    {
        $stockId = 1;
        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2018',
            'validTo' => '31-12-2018',
            'initialStock' => 100,
            'irhpPermitType' => [
                'id' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                'name' => [
                    'description' => 'Annual Multilateral (EU and EEA)'
                ]
            ]
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $mockPlaceholder = m::mock(Placeholder::class)
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('Permits')
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('Type: Annual Multilateral (EU and EEA) Validity: 01/01/2018 to 31/12/2018 Quota: 100')
                    ->getMock()
            )
            ->getMock();

        $mockNavigation = m::mock(Navigation::class)
            ->shouldReceive('findOneBy')->once()->with('id', 'admin-dashboard/admin-permits/sectors')->andReturn(
                m::mock(Navigation::class)->shouldReceive('setVisible')->once()->with(false)->getMock()
            )->getMock()
            ->shouldReceive('findOneBy')->once()->with('id', 'admin-dashboard/admin-permits/scoring')->andReturn(
                m::mock(Navigation::class)->shouldReceive('setVisible')->once()->with(false)->getMock()
            )->getMock()
            ->shouldReceive('findOneBy')->once()->with('id', 'admin-dashboard/admin-permits/jurisdiction')->andReturn(
                m::mock(Navigation::class)->shouldReceive('setVisible')->once()->with(false)->getMock()
            )->getMock();

        $this->sut->setNavigationService($mockNavigation);

        $mockViewHelperManager = m::mock(HelperPluginManager::class)
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $event = new RouteParam();
        $event->setValue($stockId);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);
        $mockNavigation = m::mock(Navigation::class);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);

        $sut = new IrhpPermitAdminFurniture();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $sut->getQuerySender());
        $this->assertSame($mockCommandSender, $sut->getCommandSender());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
    }


    public function testGetPageTitle()
    {
        $mockUrl = m::mock(UrlHelperService::class);
        $mockUrl->shouldReceive('__invoke');
    }
}
