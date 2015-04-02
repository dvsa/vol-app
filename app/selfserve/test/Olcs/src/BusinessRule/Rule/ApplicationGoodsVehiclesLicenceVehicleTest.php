<?php

/**
 * Application Goods Vehicles Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\BusinessRule\Rule;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessRule\Rule\ApplicationGoodsVehiclesLicenceVehicle;
use OlcsTest\Bootstrap;

/**
 * Application Goods Vehicles Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationGoodsVehiclesLicenceVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $brm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->brm = m::mock('\Common\BusinessRule\BusinessRuleManager')->makePartial();

        $this->sut = new ApplicationGoodsVehiclesLicenceVehicle();

        $this->sut->setServiceLocator($this->sm);
        $this->sut->setBusinessRuleManager($this->brm);
    }

    public function testValidateEdit()
    {
        $data = ['foo' => 'bar'];
        $mode = 'edit';
        $vehicleId = 111;
        $licenceId = 222;
        $applicationId = 333;

        // Mocks
        $mockVariationGoodsVehiclesLicenceVehicle = m::mock('\Common\BusinessRule\BusinessRuleInterface');
        $this->brm->setService('VariationGoodsVehiclesLicenceVehicle', $mockVariationGoodsVehiclesLicenceVehicle);

        // Expecations
        $mockVariationGoodsVehiclesLicenceVehicle->shouldReceive('validate')
            ->once()
            ->with($data, $mode, $vehicleId, $licenceId, $applicationId)
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->validate($data, $mode, $vehicleId, $licenceId, $applicationId));
    }

    public function testValidateAdd()
    {
        $data = ['foo' => 'bar'];
        $mode = 'add';
        $vehicleId = 111;
        $licenceId = 222;
        $applicationId = 333;
        $expected = ['foo' => 'bar', 'specifiedDate' => '2014-01-01'];

        // Mocks
        $mockDate = m::mock();

        $this->sm->setService('Helper\Date', $mockDate);

        $mockVariationGoodsVehiclesLicenceVehicle = m::mock('\Common\BusinessRule\BusinessRuleInterface');
        $this->brm->setService('VariationGoodsVehiclesLicenceVehicle', $mockVariationGoodsVehiclesLicenceVehicle);

        // Expecations
        $mockDate->shouldReceive('getDate')
            ->andReturn('2014-01-01');

        $mockVariationGoodsVehiclesLicenceVehicle->shouldReceive('validate')
            ->once()
            ->with($data, $mode, $vehicleId, $licenceId, $applicationId)
            ->andReturn($data);

        $this->assertEquals($expected, $this->sut->validate($data, $mode, $vehicleId, $licenceId, $applicationId));
    }
}
