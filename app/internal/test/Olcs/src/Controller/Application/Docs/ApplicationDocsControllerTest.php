<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Application\Docs;

use Common\RefData;
use Common\Service\Data\PluginManager as DataServiceManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\Http\Request;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Application\Docs\ApplicationDocsController;
use Olcs\Service\Data\DocumentSubCategory;

final class ApplicationDocsControllerTest extends MockeryTestCase
{
    private ApplicationDocsController $sut;

    protected function setUp(): void
    {
        $scriptFactory = m::mock(ScriptFactory::class);
        $formHelper = m::mock(FormHelperService::class);
        $tableFactory = m::mock(TableFactory::class);
        $viewHelperManager = m::mock(HelperPluginManager::class);
        $dataServiceManager = m::mock(DataServiceManager::class);
        $oppositionHelper = m::mock(OppositionHelperService::class);
        $complaintsHelper = m::mock(ComplaintsHelperService::class);
        $flashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $docSubCategoryDataService = m::mock(DocumentSubCategory::class);
        $translationHelper = m::mock(TranslationHelperService::class);
        $navigation = m::mock('navigation');

        $this->sut = m::mock(ApplicationDocsController::class, [
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dataServiceManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $docSubCategoryDataService,
            $translationHelper,
            $navigation,
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testExternalDocumentsAreNotListedForUnsubmittedApplications(): void
    {
        $controller = $this->sut;

        $controller->shouldReceive('getFromRoute')->with('application')->twice()->andReturn(123);
        $controller->shouldReceive('getLicenceIdForApplication')->with(123)->once()->andReturn(456);
        $controller->shouldReceive('getStatusForApplication')->with(123)->once()->andReturn(
            RefData::APPLICATION_STATUS_NOT_SUBMITTED
        );

        $expectedFilters = [
            'sort' => 'issuedDate',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 10,
            'licence' => 456,
            'application' => 123,
            'showDocs' => FilterOptions::EXCLUDE_IRHP,
            'isExternal' => 'N',
        ];

        $table = new \stdClass();
        $view = new ViewModel();

        $controller->shouldReceive('getDocumentsTable')->with($expectedFilters)->once()->andReturn($table);
        $controller->shouldReceive('getViewWithApplication')->with(['table' => $table])->once()->andReturn($view);
        $controller->shouldReceive('loadScripts')->with(['documents', 'table-actions'])->once();
        $controller->shouldReceive('renderView')->with($view)->once()->andReturn('RENDERED');

        self::assertSame('RENDERED', $controller->documentsAction());
    }

    public function testExternalDocumentsAreListedForSubmittedApplications(): void
    {
        $controller = $this->sut;

        $controller->shouldReceive('getFromRoute')->with('application')->twice()->andReturn(123);
        $controller->shouldReceive('getLicenceIdForApplication')->with(123)->once()->andReturn(456);
        $controller->shouldReceive('getStatusForApplication')->with(123)->once()->andReturn(
            RefData::APPLICATION_STATUS_UNDER_CONSIDERATION
        );

        $expectedFilters = [
            'sort' => 'issuedDate',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 10,
            'licence' => 456,
            'application' => 123,
            'showDocs' => FilterOptions::EXCLUDE_IRHP,
        ];

        $table = new \stdClass();
        $view = new ViewModel();

        $controller->shouldReceive('getDocumentsTable')->with($expectedFilters)->once()->andReturn($table);
        $controller->shouldReceive('getViewWithApplication')->with(['table' => $table])->once()->andReturn($view);
        $controller->shouldReceive('loadScripts')->with(['documents', 'table-actions'])->once();
        $controller->shouldReceive('renderView')->with($view)->once()->andReturn('RENDERED');

        self::assertSame('RENDERED', $controller->documentsAction());
    }
}
