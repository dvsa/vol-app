<?php

/**
 * Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesPsvControllerTest extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Licence\VehiclesPsvController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group licence-vehicle-psv-controller
     */
    public function testAlterFormForLvaInIndexAction()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')
            ->andReturn(false)
            ->shouldReceive('getPost')
            ->andReturn([])
            ->getMock();

        $mockValidator = m::mock()
            ->shouldReceive('setRows')
            ->with([0, 0, 0])
            ->shouldReceive('setCrud')
            ->with(false)
            ->getMock();

        $data = [
            'version' => 1,
            'hasEnteredReg' => 'Y'
        ];

        $form = m::mock('Zend\Form\Form')
            ->shouldReceive('get')
            ->with('small')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('rows')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(0)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('medium')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('rows')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(0)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('large')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('rows')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(0)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('data')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('get')
                    ->with('hasEnteredReg')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('getValidatorChain')
                        ->andReturn(
                            m::mock()
                            ->shouldReceive('attach')
                            ->with($mockValidator)
                            ->getMock()
                        )
                        ->getMock()
                    )
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('has')
            ->andReturn(false)
            ->shouldReceive('remove')
            ->shouldReceive('setData')
            ->with(['data' => $data])
            ->andReturnSelf()
            ->getMock();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getLvaEntityService')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDataForVehiclesPsv')
                ->with(1)
                ->andReturn($data)
                ->getMock()
            )
            ->shouldReceive('getIdentifier')
            ->andReturn(1)
            ->shouldReceive('getTypeOfLicenceData')
            ->andReturn(['licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL])
            ->shouldReceive('render')
            ->with('vehicles_psv', $form)
            ->andReturn('RENDER');

        $mockFormHelper = m::mock()
            ->shouldReceive('createForm')
            ->with('Lva\PsvVehicles')
            ->andReturn($form)
            ->shouldReceive('remove')
            ->with($form, 'data->notice')
            ->shouldReceive('remove')
            ->with($form, 'data->hasEnteredReg')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('oneRowInTablesRequired', $mockValidator);

        $mockScript = m::mock()
            ->shouldReceive('loadFile')
            ->with('vehicle-psv')
            ->getMock();

        $this->sm->setService('Script', $mockScript);
        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
