<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\ApplicationPsvVehiclesVehicle;

/**
 * Application Psv Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPsvVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $formService;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->formService = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new ApplicationPsvVehiclesVehicle($this->formHelper, $this->formService);
    }

    public function testGetFormEdit(): void
    {
        $mockRequest = m::mock();
        $params = [
            'mode' => 'edit',
            'location' => 'internal',
            'isRemoved' => false,
        ];

        // Mocks
        $mockForm = m::mock(\Laminas\Form\FormInterface::class);
        $mockPsvVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $mockGenericVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');

        $this->formService->setService('lva-psv-vehicles-vehicle', $mockPsvVehiclesVehicle);
        $this->formService->setService('lva-generic-vehicles-vehicle', $mockGenericVehiclesVehicle);

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\PsvVehiclesVehicle', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->discNo');

        $mockPsvVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $mockGenericVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm, $params);

        // <<-- START SUT::alterForm

        $mockLicenceVehicle = m::mock(\Laminas\Form\ElementInterface::class);
        $mockSpecifiedDate = m::mock()
            ->shouldReceive('getYearElement')
            ->andReturn(
                m::mock()
                ->shouldReceive('setValue')
                ->with('')
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getMonthElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getDayElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getHourElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getMinuteElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $mockRemovalDate = m::mock();

        $mockForm->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn($mockLicenceVehicle);

        $mockLicenceVehicle->shouldReceive('get')
            ->with('specifiedDate')
            ->andReturn($mockSpecifiedDate)
            ->shouldReceive('get')
            ->with('removalDate')
            ->andReturn($mockRemovalDate);

        $this->formHelper->shouldReceive('disableDateElement')
            ->with($mockSpecifiedDate)
            ->shouldReceive('disableDateElement')
            ->with($mockRemovalDate);

        // <<-- END SUT::alterForm

        $form = $this->sut->getForm($mockRequest, $params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormAdd(): void
    {
        $mockRequest = m::mock();
        $params = [
            'mode' => 'add',
            'location' => 'internal',
            'isRemoved' => false,
        ];

        // Mocks
        $mockForm = m::mock(\Laminas\Form\FormInterface::class);
        $mockPsvVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $mockGenericVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');

        $this->formService->setService('lva-psv-vehicles-vehicle', $mockPsvVehiclesVehicle);
        $this->formService->setService('lva-generic-vehicles-vehicle', $mockGenericVehiclesVehicle);

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\PsvVehiclesVehicle', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->discNo');

        $mockPsvVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $mockGenericVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm, $params);

        // <<-- START SUT::alterForm

        $mockLicenceVehicle = m::mock(\Laminas\Form\ElementInterface::class);
        $mockSpecifiedDate = m::mock()
            ->shouldReceive('getYearElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getMonthElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getDayElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getHourElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getMinuteElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $mockRemovalDate = m::mock();

        $mockForm->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn($mockLicenceVehicle);

        $mockLicenceVehicle->shouldReceive('get')
            ->with('specifiedDate')
            ->andReturn($mockSpecifiedDate)
            ->shouldReceive('get')
            ->with('removalDate')
            ->andReturn($mockRemovalDate);

        $this->formHelper->shouldReceive('disableDateElement')
            ->with($mockSpecifiedDate)
            ->shouldReceive('disableDateElement')
            ->with($mockRemovalDate);

        // <<-- END SUT::alterForm

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'vehicle-history-table');

        $form = $this->sut->getForm($mockRequest, $params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormRemoved(): void
    {
        $mockRequest = m::mock();
        $params = [
            'mode' => 'add',
            'location' => 'internal',
            'isRemoved' => true,
        ];

        // Mocks
        $mockForm = m::mock(\Laminas\Form\FormInterface::class);
        $mockPsvVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $mockGenericVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');

        $this->formService->setService('lva-psv-vehicles-vehicle', $mockPsvVehiclesVehicle);
        $this->formService->setService('lva-generic-vehicles-vehicle', $mockGenericVehiclesVehicle);

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\PsvVehiclesVehicle', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->discNo');

        $mockPsvVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $mockGenericVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm, $params);

        // <<-- START SUT::alterForm

        $mockLicenceVehicle = m::mock(\Laminas\Form\ElementInterface::class);
        $mockSpecifiedDate = m::mock()
            ->shouldReceive('getYearElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getMonthElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getDayElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getHourElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getMinuteElement')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $mockRemovalDate = m::mock();
        $mockFormActions = m::mock(\Laminas\Form\ElementInterface::class);

        $mockForm->shouldReceive('get')
            ->with('licence-vehicle')
            ->andReturn($mockLicenceVehicle)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($mockFormActions);

        $mockLicenceVehicle->shouldReceive('get')
            ->with('specifiedDate')
            ->andReturn($mockSpecifiedDate)
            ->shouldReceive('get')
            ->with('removalDate')
            ->andReturn($mockRemovalDate);

        $this->formHelper->shouldReceive('disableDateElement')
            ->with($mockSpecifiedDate)
            ->shouldReceive('disableDateElement')
            ->with($mockRemovalDate);

        $mockFormActions->shouldReceive('remove')
            ->with('submit')
            ->once();
        // <<-- END SUT::alterForm

        $mockData = m::mock(\Laminas\Form\ElementInterface::class);

        $mockForm->shouldReceive('get')
            ->with('data')
            ->andReturn($mockData);

        $mockData->shouldReceive('has')
            ->with('makeModel')
            ->andReturn(true);

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'vehicle-history-table')
            ->shouldReceive('disableElement')
            ->with($mockForm, 'data->vrm')
            ->shouldReceive('disableElement')
            ->with($mockForm, 'data->makeModel')
            ->shouldReceive('disableElements')
            ->with($mockLicenceVehicle);

        $form = $this->sut->getForm($mockRequest, $params);

        $this->assertSame($mockForm, $form);
    }
}
