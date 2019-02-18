<?php

namespace OlcsTest\Controller;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\IndexController;
use Olcs\Service\Data\IrhpPermitPrintCountry;
use Olcs\Service\Data\IrhpPermitPrintStock;
use OlcsTest\Bootstrap;
use Zend\View\Model\JsonModel;

/**
 * Index Controller Test
 */
class IndexControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock(IndexController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider dpTestEntityListAction
     */
    public function testEntityListAction($type, $value, $dataService, $mockDataService, $expected)
    {
        $list = [11 => 'ABC', 12 => 'DEF'];

        $this->sut->shouldReceive('params')->with('type')->once()->andReturn($type);
        $this->sut->shouldReceive('params')->with('value')->once()->andReturn($value);

        $mockDataService->shouldReceive('fetchListOptions')->withNoArgs()->andReturn($list);

        $this->sm->setService($dataService, $mockDataService);

        $view = $this->sut->entityListAction();

        $this->assertInstanceOf(JsonModel::class, $view);
        $this->assertEquals($expected, $view->serialize());
    }

    public function dpTestEntityListAction()
    {
        $value = 100;

        $irhpPermitPrintCountry = m::mock(IrhpPermitPrintCountry::class);
        $irhpPermitPrintCountry->shouldReceive('setIrhpPermitType')->with($value)->andReturnSelf();

        $irhpPermitPrintStockByCountry = m::mock(IrhpPermitPrintStock::class);
        $irhpPermitPrintStockByCountry->shouldReceive('setIrhpPermitType')
            ->with(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID)
            ->andReturnSelf();
        $irhpPermitPrintStockByCountry->shouldReceive('setCountry')->with($value)->andReturnSelf();

        $irhpPermitPrintStockByType = m::mock(IrhpPermitPrintStock::class);
        $irhpPermitPrintStockByType->shouldReceive('setIrhpPermitType')->with($value)->andReturnSelf();

        return [
            'irhp-permit-print-country' => [
                'type' => 'irhp-permit-print-country',
                'value' => $value,
                'dataService' => IrhpPermitPrintCountry::class,
                'mockDataService' => $irhpPermitPrintCountry,
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],
            'irhp-permit-print-stock-by-country' => [
                'type' => 'irhp-permit-print-stock-by-country',
                'value' => $value,
                'dataService' => IrhpPermitPrintStock::class,
                'mockDataService' => $irhpPermitPrintStockByCountry,
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],
            'irhp-permit-print-stock-by-type' => [
                'type' => 'irhp-permit-print-stock-by-type',
                'value' => $value,
                'dataService' => IrhpPermitPrintStock::class,
                'mockDataService' => $irhpPermitPrintStockByType,
                'expected'
                    => '[{"value":"","label":"Please select"},{"value":11,"label":"ABC"},{"value":12,"label":"DEF"}]',
            ],
        ];
    }
}
