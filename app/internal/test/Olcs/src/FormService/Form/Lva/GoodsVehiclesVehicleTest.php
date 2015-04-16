<?php

/**
 * Goods Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\GoodsVehiclesVehicle;
use OlcsTest\Bootstrap;

/**
 * Goods Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new GoodsVehiclesVehicle();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterFormAdd()
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'add'
        ];

        $mockForm->shouldReceive('remove')
            ->with('vehicle-history-table');

        $this->sut->alterForm($mockForm, $params);
    }

    public function testAlterFormEdit()
    {
        $mockForm = m::mock();
        $params = [
            'id' => 111,
            'mode' => 'edit'
        ];
        $stubbedTableData = [
            'Results' => [
                [
                    'foo' => 'bar'
                ]
            ]
        ];

        // Mocks
        $mockTable = m::mock('\Common\Service\Table\TableBuilder');
        $mockTableBuilder = m::mock();
        $mockHistoryTable = m::mock('\Zend\Form\Fieldset');
        $mockLicenceVehicle = m::mock();
        $mockVehicleHistory = m::mock();

        $this->sm->setService('Entity\LicenceVehicle', $mockLicenceVehicle);
        $this->sm->setService('Entity\VehicleHistoryView', $mockVehicleHistory);
        $this->sm->setService('Table', $mockTableBuilder);

        // Expectations
        $mockForm->shouldReceive('get')
            ->with('vehicle-history-table')
            ->andReturn($mockHistoryTable);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('lva-vehicles-history', $stubbedTableData)
            ->andReturn($mockTable);

        $mockLicenceVehicle->shouldReceive('getVrm')
            ->with(111)
            ->andReturn('ABC123');

        $mockVehicleHistory->shouldReceive('getDataForVrm')
            ->with('ABC123')
            ->andReturn($stubbedTableData);

        $this->formHelper->shouldReceive('populateFormTable')
            ->with($mockHistoryTable, $mockTable);

        $this->sut->alterForm($mockForm, $params);
    }
}
