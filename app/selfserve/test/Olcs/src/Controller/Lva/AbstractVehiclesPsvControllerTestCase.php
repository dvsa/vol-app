<?php

/**
 * Abstract Vehicles PSV Controller Test Case
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Abstract Vehicles PSV Controller Test Case
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractVehiclesPsvControllerTestCase extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    protected $controllerName;

    public function setUp()
    {
        $this->markTestSkipped();
        $this->sut = m::mock($this->controllerName)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Mock AbstractVehiclePsvController
     */
    public function mockAbstractVehiclePsvController()
    {
        $id = 69;

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
            'id' => $id,
            'version' => 1,
            'hasEnteredReg' => 'Y',
            'licence' => ['licenceVehicles' => []],
            'totAuthVehicles'       => 0,
            'totAuthSmallVehicles'  => 0,
            'totAuthMediumVehicles' => 0,
            'totAuthLargeVehicles'  => 0,
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
                    ->shouldReceive('get')
                    ->with('table')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getTable')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('removeAction')
                                    ->with('edit')
                                    ->getMock()
                            )
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
                    ->shouldReceive('get')
                    ->with('table')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getTable')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('removeAction')
                                    ->with('edit')
                                    ->getMock()
                            )
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
            ->andReturn(false, false, false, true, true)
            ->shouldReceive('remove')
            ->shouldReceive('setData')
            ->with(['data' => ['version' => 1, 'hasEnteredReg' => 'Y']])
            ->andReturnSelf()
            ->getMock();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getLvaEntityService')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDataForVehiclesPsv')
                ->with($id)
                ->andReturn($data)
                ->getMock()
            )
            ->shouldReceive('getIdentifier')
            ->andReturn($id)
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
            ->shouldReceive('loadFiles')
            ->with(['lva-crud', 'vehicle-psv'])
            ->getMock();

        $this->sm->setService('Script', $mockScript);

        // stub the mapping between type and psv type that is now in entity service
        $map = [
            'small'  => 'vhl_t_a',
            'medium' => 'vhl_t_b',
            'large'  => 'vhl_t_c',
        ];
        $this->sm->setService(
            'Entity\Vehicle',
            m::mock()
                ->shouldReceive('getTypeMap')
                    ->andReturn($map)
                ->shouldReceive('getPsvTypeFromType')
                    ->andReturnUsing(
                        function ($type) use ($map) {
                            return isset($map[$type]) ? $map[$type] : null;
                        }
                    )
                ->getMock()
        );

        $mockAdapter = m::mock('\Common\Controller\Lva\Interfaces\AdapterInterface')
            ->shouldReceive('getVehicleCountByPsvType')
                ->andReturn(0)
            ->shouldReceive('warnIfAuthorityExceeded')
            ->getMock();

        $this->sut->setAdapter($mockAdapter);
    }
}
