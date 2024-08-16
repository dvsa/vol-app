<?php

namespace OlcsTest\Controller;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\IndexController;
use Olcs\Service\Data\DocumentSubCategory;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;
use Olcs\Service\Data\IrhpPermitPrintCountry;
use Olcs\Service\Data\IrhpPermitPrintRangeType;
use Olcs\Service\Data\IrhpPermitPrintStock;
use Olcs\Service\Data\ScannerSubCategory;
use Olcs\Service\Data\SubCategory;
use Olcs\Service\Data\SubCategoryDescription;
use Olcs\Service\Data\TaskSubCategory;
use Olcs\Service\Data\UserListInternal;
use Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsers;
use Laminas\View\Model\JsonModel;

/**
 * Index Controller Test
 */
class IndexControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $mockScriptFactory;
    protected $mockFormHelper;
    protected $mockTableFactory;
    protected $mockViewHelperManager;
    protected $mockFlashMessengerHelper;
    protected $mockUserListInternalDataService;
    protected $mockUserListInternalExcludingDataService;
    protected $mockSubCategoryDataService;
    protected $mockTaskSubCategoryDataService;
    protected $mockDocumentSubCategoryDataService;
    protected $mockDocumentSubCategoryWithDocsDataService;
    protected $mockScannerSubCategoryDataService;
    protected $mockSubCategoryDescriptionDataService;
    protected $mockIrhpPermitPrintCountryDataService;
    protected $mockIrhpPermitPrintStockDataService;
    protected $mockIrhpPermitPrintRangeTypeDataService;


    public function setUp(): void
    {
        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockTableFactory = m::mock(TableFactory::class);
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockUserListInternalDataService = m::mock(UserListInternal::class);
        $this->mockUserListInternalExcludingDataService = m::mock(UserListInternalExcludingLimitedReadOnlyUsers::class);
        $this->mockSubCategoryDataService = m::mock(SubCategory::class);
        $this->mockTaskSubCategoryDataService = m::mock(TaskSubCategory::class);
        $this->mockDocumentSubCategoryDataService = m::mock(DocumentSubCategory::class);
        $this->mockDocumentSubCategoryWithDocsDataService = m::mock(DocumentSubCategoryWithDocs::class);
        $this->mockScannerSubCategoryDataService = m::mock(ScannerSubCategory::class);
        $this->mockSubCategoryDescriptionDataService = m::mock(SubCategoryDescription::class);
        $this->mockIrhpPermitPrintCountryDataService = m::mock(IrhpPermitPrintCountry::class);
        $this->mockIrhpPermitPrintStockDataService = m::mock(IrhpPermitPrintStock::class);
        $this->mockIrhpPermitPrintRangeTypeDataService = m::mock(IrhpPermitPrintRangeType::class);

        $this->sut = m::mock(IndexController::class, [
            $this->mockScriptFactory,
            $this->mockFormHelper,
            $this->mockTableFactory,
            $this->mockViewHelperManager,
            $this->mockFlashMessengerHelper,
            $this->mockUserListInternalDataService,
            $this->mockUserListInternalExcludingDataService,
            $this->mockSubCategoryDataService,
            $this->mockTaskSubCategoryDataService,
            $this->mockDocumentSubCategoryDataService,
            $this->mockDocumentSubCategoryWithDocsDataService,
            $this->mockScannerSubCategoryDataService,
            $this->mockSubCategoryDescriptionDataService,
            $this->mockIrhpPermitPrintCountryDataService,
            $this->mockIrhpPermitPrintStockDataService,
            $this->mockIrhpPermitPrintRangeTypeDataService
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * @dataProvider dpTestEntityListAction
     */
    public function testEntityListAction($type, $value, $dataService, $mockDataService, $expected)
    {
        $list = [11 => 'ABC', 12 => 'DEF'];

        $this->sut->shouldReceive('params')->with('type')->once()->andReturn($type);
        $this->sut->shouldReceive('params')->with('value')->once()->andReturn($value);

        $mockDataService = $this->$mockDataService;
        $mockDataService->shouldReceive('setIrhpPermitType')->with($value)->andReturnSelf();
        $mockDataService->shouldReceive('setIrhpPermitType')
            ->with(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID)
            ->andReturnSelf();
        $mockDataService->shouldReceive('setCountry')->with($value)->andReturnSelf();
        $mockDataService->shouldReceive('setIrhpPermitStock')->with($value)->andReturnSelf();
        $mockDataService->shouldReceive('fetchListOptions')->withNoArgs()->andReturn($list);

        $view = $this->sut->entityListAction();

        $this->assertInstanceOf(JsonModel::class, $view);
        $this->assertEquals($expected, $view->serialize());
    }

    public function dpTestEntityListAction()
    {
        $value = 100;

        return [
            'irhp-permit-print-country' => [
                'type' => 'irhp-permit-print-country',
                'value' => $value,
                'dataService' => IrhpPermitPrintCountry::class,
                'mockDataService' => 'mockIrhpPermitPrintCountryDataService',
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],
            'irhp-permit-print-stock-by-country' => [
                'type' => 'irhp-permit-print-stock-by-country',
                'value' => $value,
                'dataService' => IrhpPermitPrintStock::class,
                'mockDataService' => 'mockIrhpPermitPrintStockDataService',
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],
            'irhp-permit-print-stock-by-type' => [
                'type' => 'irhp-permit-print-stock-by-type',
                'value' => $value,
                'dataService' => IrhpPermitPrintStock::class,
                'mockDataService' => 'mockIrhpPermitPrintStockDataService',
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],
            'irhp-permit-print-range-type-by-stock' => [
                'type' => 'irhp-permit-print-range-type-by-stock',
                'value' => $value,
                'dataService' => IrhpPermitPrintRangeType::class,
                'mockDataService' => 'mockIrhpPermitPrintRangeTypeDataService',
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],

        ];
    }
}
