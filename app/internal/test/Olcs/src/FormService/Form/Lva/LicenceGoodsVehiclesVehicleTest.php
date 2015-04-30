<?php

/**
 * Licence Goods Vehicles Vehicle Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceGoodsVehiclesVehicle;
use OlcsTest\Bootstrap;

/**
 * Licence Goods Vehicles Vehicle Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceGoodsVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = m::mock('Olcs\FormService\Form\Lva\LicenceGoodsVehiclesVehicle')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test alter form vehicle with removed vehicle
     *
     * @group licenceGoodsVehiclesVehicle
     */
    public function testAlterFormVehicleRemoved()
    {
        $params = [
            'isRemoved' => true,
            'mode' => 'edit'
        ];
        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('specifiedDate')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setShouldCreateEmptyOption')
                    ->with(false)
                    ->once()
                    ->getMock()
                )
                ->shouldReceive('get')
                ->with('removalDate')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getDayElement')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('removeAttribute')
                        ->with('disabled')
                        ->once()
                        ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getMonthElement')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('removeAttribute')
                        ->with('disabled')
                        ->once()
                        ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getYearElement')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('removeAttribute')
                        ->with('disabled')
                        ->once()
                        ->getMock()
                    )
                    ->once()
                    ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('id')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('removeAttribute')
                    ->with('disabled')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('version')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('removeAttribute')
                    ->with('disabled')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('cancel')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setAttribute')
                    ->with('disabled', false)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('submit')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('removeAttribute')
                    ->with('disabled')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('security')
            ->andReturn(
                m::mock()
                ->shouldReceive('removeAttribute')
                ->with('disabled')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableElements')
            ->with($mockForm)
            ->once()
            ->shouldReceive('disableEmptyValidation')
            ->with($mockForm)
            ->andReturn('form')
            ->once()
            ->getMock();

        $this->sut
            ->shouldReceive('getFormHelper')
            ->andReturn($mockFormHelper)
            ->shouldReceive('getFormServiceLocator')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('lva-goods-vehicles-vehicle')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm, $params)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('lva-generic-vehicles-vehicle')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm, $params)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            );

        $this->assertEquals('form', $this->sut->alterForm($mockForm, $params));

    }

    /**
     * Test alter form vehicle
     *
     * @group licenceGoodsVehiclesVehicle
     */
    public function testAlterFormVehicle()
    {
        $params = [
            'isRemoved' => false,
            'mode' => 'edit'
        ];
        $mockForm = m::mock('Common\Service\Form\Form')
            ->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('specifiedDate')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setShouldCreateEmptyOption')
                    ->with(false)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableElement')
            ->with($mockForm, 'licence-vehicle->removalDate')
            ->getMock();

        $this->sut
            ->shouldReceive('getFormHelper')
            ->andReturn($mockFormHelper)
            ->shouldReceive('getFormServiceLocator')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('lva-goods-vehicles-vehicle')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm, $params)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('lva-generic-vehicles-vehicle')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm, $params)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            );
        $this->assertInstanceOf('Common\Service\Form\Form', $this->sut->alterForm($mockForm, $params));
    }
}
