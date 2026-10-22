<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Docs;

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
use Olcs\Controller\Licence\Docs\LicenceDocsController;
use Olcs\Service\Data\DocumentSubCategory;

final class LicenceDocsControllerTest extends MockeryTestCase
{
    private LicenceDocsController $sut;

    protected function setUp(): void
    {
        $scriptFactory = m::mock(ScriptFactory::class);
        $formHelper = m::mock(FormHelperService::class);
        $tableFactory = m::mock(TableFactory::class);
        $viewHelperManager = m::mock(HelperPluginManager::class);
        $oppositionHelper = m::mock(OppositionHelperService::class);
        $complaintsHelper = m::mock(ComplaintsHelperService::class);
        $translationHelper = m::mock(TranslationHelperService::class);
        $docSubCategoryDataService = m::mock(DocumentSubCategory::class);
        $navigation = m::mock('navigation');
        $flashMessengerHelper = m::mock(FlashMessengerHelperService::class);

        $this->sut = m::mock(LicenceDocsController::class, [
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $translationHelper,
            $docSubCategoryDataService,
            $navigation,
            $flashMessengerHelper,
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testHidesDocumentsUnsubmittedApplications(): void
    {
        $controller = $this->sut;

        $controller->shouldReceive('getFromRoute')->with('licence')->andReturn(123);

        $expectedFilters = [
            'sort' => 'issuedDate',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 10,
            'showDocs' => FilterOptions::EXCLUDE_IRHP,
            'licence' => 123,
            'hideUnsubmittedApplicationDocs' => 'Y'
        ];

        $table = new \stdClass();
        $view = new ViewModel();

        $controller->shouldReceive('getDocumentsTable')->with($expectedFilters)->once()->andReturn($table);
        $controller->shouldReceive('getViewWithLicence')->with(['table' => $table])->once()->andReturn($view);
        $controller->shouldReceive('loadScripts')->with(['documents', 'table-actions'])->once();
        $controller->shouldReceive('renderView')->with($view)->once()->andReturn('RENDERED');

        self::assertSame('RENDERED', $controller->documentsAction());
    }
}
