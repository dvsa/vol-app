<?php

declare(strict_types=1);

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
final class IndexControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $mockIrhpPermitPrintCountryDataService;
    protected $mockIrhpPermitPrintStockDataService;
    protected $mockIrhpPermitPrintRangeTypeDataService;


    #[\Override]
    public function setUp(): void
    {
        $mockScriptFactory = m::mock(ScriptFactory::class);
        $mockFormHelper = m::mock(FormHelperService::class);
        $mockTableFactory = m::mock(TableFactory::class);
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $mockUserListInternalDataService = m::mock(UserListInternal::class);
        $mockUserListInternalExcludingDataService = m::mock(UserListInternalExcludingLimitedReadOnlyUsers::class);
        $mockSubCategoryDataService = m::mock(SubCategory::class);
        $mockTaskSubCategoryDataService = m::mock(TaskSubCategory::class);
        $mockDocumentSubCategoryDataService = m::mock(DocumentSubCategory::class);
        $mockDocumentSubCategoryWithDocsDataService = m::mock(DocumentSubCategoryWithDocs::class);
        $mockScannerSubCategoryDataService = m::mock(ScannerSubCategory::class);
        $mockSubCategoryDescriptionDataService = m::mock(SubCategoryDescription::class);
        $this->mockIrhpPermitPrintCountryDataService = m::mock(IrhpPermitPrintCountry::class);
        $this->mockIrhpPermitPrintStockDataService = m::mock(IrhpPermitPrintStock::class);
        $this->mockIrhpPermitPrintRangeTypeDataService = m::mock(IrhpPermitPrintRangeType::class);

        $this->sut = m::mock(IndexController::class, [
            $mockScriptFactory,
            $mockFormHelper,
            $mockTableFactory,
            $mockViewHelperManager,
            $mockFlashMessengerHelper,
            $mockUserListInternalDataService,
            $mockUserListInternalExcludingDataService,
            $mockSubCategoryDataService,
            $mockTaskSubCategoryDataService,
            $mockDocumentSubCategoryDataService,
            $mockDocumentSubCategoryWithDocsDataService,
            $mockScannerSubCategoryDataService,
            $mockSubCategoryDescriptionDataService,
            $this->mockIrhpPermitPrintCountryDataService,
            $this->mockIrhpPermitPrintStockDataService,
            $this->mockIrhpPermitPrintRangeTypeDataService
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestEntityListAction')]
    public function testEntityListAction(mixed $type, mixed $value, mixed $dataService, mixed $mockDataService, mixed $expected): void
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

    public static function dpTestEntityListAction(): \Iterator
    {
        $value = 100;
        yield 'irhp-permit-print-country' => [
            'type' => 'irhp-permit-print-country',
            'value' => $value,
            'dataService' => IrhpPermitPrintCountry::class,
            'mockDataService' => 'mockIrhpPermitPrintCountryDataService',
            'expected'
                => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
        ];
        yield 'irhp-permit-print-stock-by-country' => [
            'type' => 'irhp-permit-print-stock-by-country',
            'value' => $value,
            'dataService' => IrhpPermitPrintStock::class,
            'mockDataService' => 'mockIrhpPermitPrintStockDataService',
            'expected'
                => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
        ];
        yield 'irhp-permit-print-stock-by-type' => [
            'type' => 'irhp-permit-print-stock-by-type',
            'value' => $value,
            'dataService' => IrhpPermitPrintStock::class,
            'mockDataService' => 'mockIrhpPermitPrintStockDataService',
            'expected'
                => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
        ];
        yield 'irhp-permit-print-range-type-by-stock' => [
            'type' => 'irhp-permit-print-range-type-by-stock',
            'value' => $value,
            'dataService' => IrhpPermitPrintRangeType::class,
            'mockDataService' => 'mockIrhpPermitPrintRangeTypeDataService',
            'expected'
                => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
        ];
    }
}
