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
use Common\Service\Entity\LicenceEntityService;

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

    public function testAlterActionFormWithGoods()
    {
        // Stub data
        $childId = 3;
        $stubbedVehicleAuths = array(
            'noOfVehiclesRequired' => 123,
            'noOfTrailersRequired' => 456
        );
        $stubbedTypeOfLicenceData = array(
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
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
        $mockLicenceService = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceService);

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

        $mockLicenceService->shouldReceive('getTypeOfLicenceData')
            ->with($licenceId)
            ->andReturn($stubbedTypeOfLicenceData);

        $mockFormHelper->shouldReceive('remove')
            ->with($mockForm, 'advertisements')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->sufficientParking')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->permission')
            ->andReturnSelf();

        // Run the method
        $this->assertSame($mockForm, $this->sut->alterActionForm($mockForm));
    }

    public function testProcessAddressLookupForm()
    {
        $mockForm = m::mock();
        $mockRequest = m::mock();

        $this->assertFalse($this->sut->processAddressLookupForm($mockForm, $mockRequest));
    }

    public function testAlterForm()
    {
        // Stubbed data
        $licenceId = 3;
        $stubbedTolData = [
            'niFlag' => 'Y',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];
        $stubbedAddressData = [
            'Results' => []
        ];
        $stubbedAuths = [
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 5
        ];

        // Going to use a real form here to component test this code, as UNIT testing it will be expensive
        $sm = \OlcsTest\Bootstrap::getServiceManager();
        $form = $sm->get('Helper\Form')->createForm('Lva\OperatingCentres');
        // As it's a component test, we will be better off not mocking the form helper
        $this->sm->setService('Helper\Form', $sm->get('Helper\Form'));

        // Mocked services
        $mockLicenceLvaAdapter = m::mock();
        $this->sm->setService('licenceLvaAdapter', $mockLicenceLvaAdapter);
        $mockLicenceEntity = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $mockLocEntity = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLocEntity);
        $mockValidator = m::mock('Zend\Validator\ValidatorInterface');
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        // Expectations
        $mockLicenceLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('alterForm')
            ->with($form)
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $mockLicenceEntity->shouldReceive('getTypeOfLicenceData')
            ->with($licenceId)
            ->andReturn($stubbedTolData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockLocEntity->shouldReceive('getAddressSummaryData')
            ->with($licenceId)
            ->andReturn($stubbedAddressData);

        $this->controller->shouldReceive('url->fromRoute')
            ->with('create_variation', ['licence' => $licenceId])
            ->andReturn('URL');

        $mockTranslator->shouldReceive('translateReplace')
            ->with('cant-increase-total-vehicles', ['URL'])
            ->andReturn('MESSAGE 1')
            ->shouldReceive('translateReplace')
            ->with('cant-increase-total-trailers', ['URL'])
            ->andReturn('MESSAGE 2');

        $mockValidator->shouldReceive('setGenericMessage')
            ->with('MESSAGE 1')
            ->shouldReceive('setPreviousValue')
            ->with(10)
            ->shouldReceive('setGenericMessage')
            ->with('MESSAGE 2')
            ->shouldReceive('setPreviousValue')
            ->with(5);

        $alteredForm = $this->sut->alterForm($form);

        $this->assertFalse($alteredForm->get('data')->has('totCommunityLicences'));
    }

    public function testAlterFormWithCommunityLicences()
    {
        // Stubbed data
        $licenceId = 3;
        $stubbedTolData = [
            'niFlag' => 'Y',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];
        $stubbedAddressData = [
            'Results' => []
        ];
        $stubbedAuths = [
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 5
        ];

        // Going to use a real form here to component test this code, as UNIT testing it will be expensive
        $sm = \OlcsTest\Bootstrap::getServiceManager();
        $form = $sm->get('Helper\Form')->createForm('Lva\OperatingCentres');
        // As it's a component test, we will be better off not mocking the form helper
        $this->sm->setService('Helper\Form', $sm->get('Helper\Form'));
        $sm->setAllowOverride(true);
        $mockViewRenderer = m::mock();
        $sm->setService('ViewRenderer', $mockViewRenderer);

        // Mocked services
        $mockLicenceLvaAdapter = m::mock();
        $this->sm->setService('licenceLvaAdapter', $mockLicenceLvaAdapter);
        $mockLicenceEntity = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $mockLocEntity = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLocEntity);
        $mockValidator = m::mock('Zend\Validator\ValidatorInterface');
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        // Expectations
        $mockLicenceLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('alterForm')
            ->with($form)
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $mockLicenceEntity->shouldReceive('getTypeOfLicenceData')
            ->with($licenceId)
            ->andReturn($stubbedTolData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockLocEntity->shouldReceive('getAddressSummaryData')
            ->with($licenceId)
            ->andReturn($stubbedAddressData);

        $this->controller->shouldReceive('url->fromRoute')
            ->with('create_variation', ['licence' => $licenceId])
            ->andReturn('URL');

        $mockTranslator->shouldReceive('translateReplace')
            ->with('cant-increase-total-vehicles', ['URL'])
            ->andReturn('MESSAGE 1')
            ->shouldReceive('translateReplace')
            ->with('cant-increase-total-trailers', ['URL'])
            ->andReturn('MESSAGE 2');

        $mockValidator->shouldReceive('setGenericMessage')
            ->with('MESSAGE 1')
            ->shouldReceive('setPreviousValue')
            ->with(10)
            ->shouldReceive('setGenericMessage')
            ->with('MESSAGE 2')
            ->shouldReceive('setPreviousValue')
            ->with(5);

        $mockViewRenderer->shouldReceive('render')
            ->andReturn('-LOCKED');

        $alteredForm = $this->sut->alterForm($form);

        $this->assertTrue($alteredForm->get('data')->has('totCommunityLicences'));
        $label = $alteredForm->get('data')->get('totCommunityLicences')->getLabel();

        $this->assertEquals('application_operating-centres_authorisation.data.totCommunityLicences-LOCKED', $label);
    }
}
