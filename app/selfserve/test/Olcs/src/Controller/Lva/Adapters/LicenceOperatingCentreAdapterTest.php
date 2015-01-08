<?php

/**
 * External Licence Operating Centre Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicenceOperatingCentreAdapter;

/**
 * Licence Operating Centre Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentreAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $controller;
    protected $sm;

    public function setUp()
    {
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new LicenceOperatingCentreAdapter();
        $this->sut->setController($this->controller);
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterActionForm()
    {
        // Stub data
        $childId = 3;
        $stubbedVehicleAuths = array(
            'noOfVehiclesRequired' => 123,
            'noOfTrailersRequired' => 456
        );
        $licenceId = 7;

        // Mock services
        $mockLoc = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLoc);
        $mockValidator = m::mock();
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);
        $mockLicenceLva = m::mock();
        $mockLicenceLva->shouldReceive('setController')->with($this->controller);
        $this->sm->setService('LicenceLvaAdapter', $mockLicenceLva);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Setup mocks
        $mockInputFilter = m::mock();
        $mockForm = m::mock('\Zend\Form\Form');
        $mockVehiclesElement = m::mock();
        $mockTrailersElement = m::mock();
        $mockValidatorChain = m::mock();
        $mockAddressElement = m::mock();
        $mockAddressFilter = m::mock();

        // Setup expectations
        $this->controller->shouldReceive('params')
            ->with('child_id')
            ->andReturn($childId);

        $mockLoc->shouldReceive('getVehicleAuths')
            ->with($childId)
            ->andReturn($stubbedVehicleAuths);

        $mockInputFilter->shouldReceive('get')
            ->with('data')
            ->andReturnSelf()
            ->shouldReceive('has')
            ->with('noOfVehiclesRequired')
            ->andReturn(true)
            ->shouldReceive('has')
            ->with('noOfTrailersRequired')
            ->andReturn(true)
            ->shouldReceive('get')
            ->with('noOfVehiclesRequired')
            ->andReturn($mockVehiclesElement)
            ->shouldReceive('get')
            ->with('noOfTrailersRequired')
            ->andReturn($mockTrailersElement);

        $mockVehiclesElement->shouldReceive('getValidatorChain')
            ->andReturn($mockValidatorChain);
        $mockTrailersElement->shouldReceive('getValidatorChain')
            ->andReturn($mockValidatorChain);

        $mockForm->shouldReceive('getInputFilter')->andReturn($mockInputFilter);

        $mockLicenceLva->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $this->controller->shouldReceive('url->fromRoute')
            ->with('create_variation', ['licence' => $licenceId])
            ->andReturn('URL');

        $mockTranslator->shouldReceive('translateReplace')
            ->with('cant-increase-vehicles', ['URL'])
            ->andReturn('VEHICLES MESSAGE');

        $mockTranslator->shouldReceive('translateReplace')
            ->with('cant-increase-trailers', ['URL'])
            ->andReturn('TRAILERS MESSAGE');

        $mockValidator->shouldReceive('setGenericMessage')
            ->with('VEHICLES MESSAGE')
            ->shouldReceive('setPreviousValue')
            ->with(123)
            ->shouldReceive('setGenericMessage')
            ->with('TRAILERS MESSAGE')
            ->shouldReceive('setPreviousValue')
            ->with(456);

        $mockValidatorChain->shouldReceive('attach')
            ->twice()
            ->with($mockValidator);

        $mockForm->shouldReceive('get')
            ->with('address')
            ->andReturn($mockAddressElement);

        $mockInputFilter->shouldReceive('get')
            ->with('address')
            ->andReturn($mockAddressFilter);

        $mockAddressElement->shouldReceive('remove')
            ->with('searchPostcode');

        $mockFormHelper->shouldReceive('disableElements')
            ->with($mockAddressElement);

        $mockFormHelper->shouldReceive('disableValidation')
            ->with($mockAddressFilter);

        $mockAddressElement->shouldReceive('get')
            ->andReturn('ELEMENT');

        $mockFormHelper->shouldReceive('lockElement')
            ->times(4)
            ->with('ELEMENT', 'operating-centre-address-requires-variation');

        // Run the method
        $this->sut->alterActionForm($mockForm);
    }
}
