<?php

declare(strict_types=1);

/**
 * Dashboard Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Common\Service\Table\DataMapper\DashboardTmApplications;
use Common\Service\Table\TableFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as DashboardQry;
use Olcs\Service\Processing\DashboardProcessingService;
use OlcsTest\Controller\Traits\ControllerTestTrait;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Common\Service\Cqrs\Response as QueryResponse;
use ReflectionClass;

/**
 * Dashboard Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected $mockDashboardProcessingService;
    protected $mockDashboardTmApplicationsDataMapper;
    protected $mockTableFactory;


    public function setUp(): void
    {
        $this->sut = m::mock(\Olcs\Controller\DashboardController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockDashboardProcessingService = m::mock(DashboardProcessingService::class);
        $this->mockDashboardTmApplicationsDataMapper = m::mock(DashboardTmApplications::class);
        $this->mockTableFactory = m::mock(TableFactory::class);

        $reflectionClass = new ReflectionClass(\Olcs\Controller\DashboardController::class);
        $reflectionProperty = $reflectionClass->getProperty('dashboardProcessingService');
        $reflectionProperty->setValue($this->sut, $this->mockDashboardProcessingService);

        $reflectionProperty = $reflectionClass->getProperty('dashboardTmApplicationsDataMapper');
        $reflectionProperty->setValue($this->sut, $this->mockDashboardTmApplicationsDataMapper);

        $reflectionProperty = $reflectionClass->getProperty('tableFactory');
        $reflectionProperty->setValue($this->sut, $this->mockTableFactory);
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true, true, true}, list{true, false, true}, list{false, true, false}, list{false, false, true}}
     */
    public static function dataProviderCorrectDashboardShown(): array
    {
        return [
            [true, true, true],
            [true, false, true],
            [false, true, false],
            [false, false, true], // this should be impossible as if you don't have either you shouldn't be on the page
        ];
    }

    /**
     *
     * @param bool $permissionSelfserveLva
     * @param bool $permissionSelfserveTmDashboard
     * @param bool $standardView
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderCorrectDashboardShown')]
    public function testCorrectDashboardShown(bool $permissionSelfserveLva, bool $permissionSelfserveTmDashboard, bool $standardView): void
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

    public function testDashboardStandard(): void
    {
        $organisationId = 45;

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
        ];
        $this->expectQuery(
            DashboardQry::class,
            ['id' => $organisationId],
            [
                'id' => $organisationId,
                'dashboard' => $dashboardData,
            ]
        );

        $this->mockDashboardProcessingService->shouldReceive('getTables')
            ->with($dashboardData)
            ->once()
            ->andReturn(['applications' => ['apps'], 'variations' => ['vars'], 'licences' => ['lics']]);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $dashboardDataResponse = m::mock(QueryResponse::class);
        $dashboardDataResponse->shouldIgnoreMissing();
        $dashboardDataResponse->shouldReceive('getResult')->andReturn($dashboardData);

        $reportToggleResponse = m::mock(QueryResponse::class);
        $reportToggleResponse->shouldIgnoreMissing();
        $reportToggleResponse->shouldReceive('getResult')->andReturn(['isEnabled' => 1]);

        $this->sut->shouldReceive('handleQuery')
                 ->andReturn($reportToggleResponse);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('dashboard', $view->getTemplate());
        $this->assertEquals(['apps'], $view->getVariable('applications'));
        $this->assertEquals(['vars'], $view->getVariable('variations'));
        $this->assertEquals(['lics'], $view->getVariable('licences'));
    }

    public function testDashboardTransportManager(): void
    {
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

        $placeholder = m::mock(Placeholder::class);
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'dashboard.tm.title')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $this->mockDashboardTmApplicationsDataMapper->shouldReceive('map')
            ->with(['service data'])
            ->once()
            ->andReturn(['mapped data']);

        $this->mockTableFactory->shouldReceive('buildTable')
            ->with('dashboard-tm-applications', ['mapped data'])
            ->once()
            ->andReturn('TABLE');

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('dashboard-tm', $view->getTemplate());
        $this->assertEquals('TABLE', $view->getVariable('applicationsTable'));
    }
}
