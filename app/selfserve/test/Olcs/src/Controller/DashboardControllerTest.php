<?php

/**
 * Dashboard Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as DashboardQry;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;

/**
 * Dashboard Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
    }

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\DashboardController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = $this->getServiceManager();
        $this->sm->setAllowOverride(true);

        $this->sut->setServiceLocator($this->sm);
    }

    public function dataProviderCorrectDashboardShown()
    {
        return [
            [true, true, true],
            [true, false, true],
            [false, true, false],
            [false, false, true], // this should be impossible as if you don't have either you shouldn't be on the page
        ];
    }

    /**
     * @dataProvider dataProviderCorrectDashboardShown
     * @param type $permissionSelfserveLva
     * @param type $permissionSelfserveTmDashboard
     * @param type $standardView
     */
    public function testCorrectDashboardShown($permissionSelfserveLva, $permissionSelfserveTmDashboard, $standardView)
    {
        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_LVA)
            ->andReturn($permissionSelfserveLva);
        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD)
            ->andReturn($permissionSelfserveTmDashboard);

        if ($standardView) {
            $this->sut->shouldReceive('transportManagerDashboardView')->never();
            $this->sut->shouldReceive('standardDashboardView')->once();
        } else {
            $this->sut->shouldReceive('transportManagerDashboardView')->once()->with();
            $this->sut->shouldReceive('standardDashboardView')->never();
        }

        $this->sut->indexAction();
    }

    /**
     * @group test123
     */
    public function testDashboardStandard()
    {
        $organisationId = 45;

        $mockDashboardProcessingService = m::mock();
        $this->sm->setService('DashboardProcessingService', $mockDashboardProcessingService);

        $mockNavigation = m::mock();
        $this->sm->setService('navigation', $mockNavigation);

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD)
            ->once()
            ->andReturn(true);
        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_LVA)
            ->once()
            ->andReturn(true);
        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $dashboardData = [
            'licences' => [],
            'applications' => [],
            'variations' => [],
            'feeCount' => 99,
            'correspondenceCount' => 123,
        ];
        $this->expectQuery(
            DashboardQry::class,
            ['id' => $organisationId],
            [
                'id' => $organisationId,
                'dashboard' => $dashboardData,
            ]
        );

        $mockDashboardProcessingService->shouldReceive('getTables')
            ->with($dashboardData)
            ->once()
            ->andReturn(['applications' => ['apps'], 'variations' => ['vars'], 'licences' => ['lics']]);

        $mockNavigation
            ->shouldReceive('findOneById')
            ->with('dashboard-fees')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 99)
                    ->getMock()
            )
            ->shouldReceive('findOneById')
            ->with('dashboard-correspondence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 123)
                    ->getMock()
            );

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('dashboard', $view->getTemplate());
        $this->assertEquals(['apps'], $view->getVariable('applications'));
        $this->assertEquals(['vars'], $view->getVariable('variations'));
        $this->assertEquals(['lics'], $view->getVariable('licences'));
    }

    public function testDashboardTransportManager()
    {
        $mockUserEntity = m::mock();
        $this->sm->setService('Entity\User', $mockUserEntity);

        $mockTable = m::mock();
        $this->sm->setService('Table', $mockTable);

        $mockDataMapper = m::mock();
        $this->sm->setService('DataMapper\DashboardTmApplications', $mockDataMapper);

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD)
            ->once()
            ->andReturn(true);
        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_LVA)
            ->once()
            ->andReturn(false);

        $mockResult = m::mock();

        $this->sut->shouldReceive('currentUser->getUserData')->with()->once()->andReturn(['id' => 77]);
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockResult);

        $mockResult->shouldReceive('getResult')->with()->once()->andReturn(['results' => ['service data']]);

        $mockDataMapper->shouldReceive('map')
            ->with(['service data'])
            ->once()
            ->andReturn(['mapped data']);

        $mockTable->shouldReceive('buildTable')
            ->with('dashboard-tm-applications', ['mapped data'])
            ->once()
            ->andReturn('TABLE');

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('dashboard-tm', $view->getTemplate());
        $this->assertEquals('TABLE', $view->getVariable('applicationsTable'));
    }
}
