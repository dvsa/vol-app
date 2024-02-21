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
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Admin\Listener\RouteParam\IrhpPermitAdminFurniture;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\Mvc as NavigationPage;
use Laminas\View\Helper\Placeholder;
use Laminas\View\HelperPluginManager;

class IrhpPermitAdminFurnitureTest extends TestCase
{
    /**
     * @var IrhpPermitAdminFurniture
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IrhpPermitAdminFurniture();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'stockId', [$this->sut, 'onIrhpPermitAdminFurniture'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function onIrhpPermitAdminSetup($irhpPermitStockData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResult = m::mock();

        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
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

        $routeParam = new RouteParam();
        $routeParam->setValue(32);

        $event = new Event(null, $routeParam);

        $this->expectException(ResourceNotFoundException::class);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    /**
     * @dataProvider dpNoAdditionalNavProvider
     */
    public function testOnIrhpPermitAdminNoAdditionalNav($permitTypeId)
    {
        $stockId = 1;
        $subTitle = 'Type: permit type description Validity: 01/01/2018 to 31/12/2018 Quota: 100';

        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2018',
            'validTo' => '31-12-2018',
            'initialStock' => 100,
            'irhpPermitType' => [
                'id' => $permitTypeId,
                'isEcmtRemoval' => false,
                'name' => [
                    'description' => 'permit type description'
                ]
            ]
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $this->getViewHelperManager($subTitle);

        $routeParam = new RouteParam();
        $routeParam->setValue($stockId);

        $event = new Event(null, $routeParam);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function dpNoAdditionalNavProvider()
    {
        return [
            [Refdata::ECMT_PERMIT_TYPE_ID],
            [Refdata::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [Refdata::ECMT_SHORT_TERM_PERMIT_TYPE_ID],
        ];
    }

    /**
     * @dataProvider dpWithAdditionalNavProvider
     */
    public function testOnIrhpPermitAdminWithAdditionalNav($permitTypeId)
    {
        $stockId = 1;
        $subTitle = 'Type: permit type description Validity: 01/01/2018 to 31/12/2018 Quota: 100';

        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2018',
            'validTo' => '31-12-2018',
            'initialStock' => 100,
            'irhpPermitType' => [
                'id' => $permitTypeId,
                'isEcmtRemoval' => false,
                'name' => [
                    'description' => 'permit type description'
                ]
            ]
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $this->getViewHelperManager($subTitle);
        $this->getMockNavigation();

        $routeParam = new RouteParam();
        $routeParam->setValue($stockId);

        $event = new Event(null, $routeParam);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function dpWithAdditionalNavProvider()
    {
        return [
            [Refdata::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
            [Refdata::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID],
            [Refdata::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID],
        ];
    }

    public function testOnIrhpPermitAdminRemovals()
    {
        $stockId = 10;
        $subTitle = 'Type: Removals permit Stock: 10 Quota: 100';

        $irhpPermitStock = [
            'id' => $stockId,
            'initialStock' => 100,
            'irhpPermitType' => [
                'id' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                'isEcmtRemoval' => true,
                'name' => [
                    'description' => 'Removals permit'
                ]
            ]
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $this->getViewHelperManager($subTitle);
        $this->getMockNavigation();

        $routeParam = new RouteParam();
        $routeParam->setValue($stockId);

        $event = new Event(null, $routeParam);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testOnIrhpPermitAdminBilateral()
    {
        $stockId = 20;
        $subTitle = 'Type: Bilateral permits (Norway) Validity: 01/01/2019 to 31/12/2019 Quota: 200';

        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2019',
            'validTo' => '31-12-2019',
            'initialStock' => 200,
            'irhpPermitType' => [
                'id' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                'isEcmtRemoval' => false,
                'name' => [
                    'description' => 'Bilateral permits'
                ]
            ],
            'country' => [
                'countryDesc' => 'Norway'
            ],
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $this->getViewHelperManager($subTitle);

        $routeParam = new RouteParam();
        $routeParam->setValue($stockId);

        $event = new Event(null, $routeParam);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testOnIrhpPermitAdminBilateralWithPermitCategory()
    {
        $stockId = 20;
        $subTitle = 'Type: Bilateral permits (Morocco - Permit category description) Validity: 01/01/2019 to 31/12/2019 Quota: 200';

        $irhpPermitStock = [
            'id' => $stockId,
            'validFrom' => '01-01-2019',
            'validTo' => '31-12-2019',
            'initialStock' => 200,
            'irhpPermitType' => [
                'id' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                'isEcmtRemoval' => false,
                'name' => [
                    'description' => 'Bilateral permits'
                ]
            ],
            'country' => [
                'countryDesc' => 'Morocco'
            ],
            'permitCategory' => [
                'description' => 'Permit category description'
            ],
        ];

        $this->onIrhpPermitAdminSetup($irhpPermitStock);

        $this->getViewHelperManager($subTitle);

        $routeParam = new RouteParam();
        $routeParam->setValue($stockId);

        $event = new Event(null, $routeParam);

        $this->sut->onIrhpPermitAdminFurniture($event);
    }

    public function testInvoke()
    {
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);
        $mockNavigation = m::mock(Navigation::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);
        $mockSl->shouldReceive('get')->with('navigation')->andReturn($mockNavigation);

        $sut = new IrhpPermitAdminFurniture();
        $service = $sut->__invoke($mockSl, IrhpPermitAdminFurniture::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $sut->getQuerySender());
        $this->assertSame($mockCommandSender, $sut->getCommandSender());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
    }

    private function getViewHelperManager($subTitle)
    {
        $titleContainer = m::mock(Placeholder\Container::class);
        $titleContainer->expects('set')->with('Permits');

        $subTitleContainer = m::mock(Placeholder\Container::class);
        $subTitleContainer->expects('set')->with($subTitle);

        $mockPlaceholder = m::mock(Placeholder::class);
        $mockPlaceholder->expects('getContainer')->with('pageTitle')->andReturn($titleContainer);
        $mockPlaceholder->expects('getContainer')->with('pageSubtitle')->andReturn($subTitleContainer);

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockViewHelperManager->expects('get')->with('placeholder')->andReturn($mockPlaceholder);

        $this->sut->setViewHelperManager($mockViewHelperManager);
    }

    private function getMockNavigation()
    {
        $sectorsPage = m::mock(NavigationPage::class);
        $sectorsPage->expects('setVisible')->with(false);

        $scoringPage = m::mock(NavigationPage::class);
        $scoringPage->expects('setVisible')->with(false);

        $jurisdictionPage = m::mock(NavigationPage::class);
        $jurisdictionPage->expects('setVisible')->with(false);

        $mockNavigation = m::mock(Navigation::class);
        $mockNavigation->expects('findOneBy')
            ->with('id', 'admin-dashboard/admin-permits/sectors')
            ->andReturn($sectorsPage);
        $mockNavigation->expects('findOneBy')
            ->with('id', 'admin-dashboard/admin-permits/scoring')
            ->andReturn($scoringPage);
        $mockNavigation->expects('findOneBy')
            ->with('id', 'admin-dashboard/admin-permits/jurisdiction')
            ->andReturn($jurisdictionPage);

        $this->sut->setNavigationService($mockNavigation);
    }
}
