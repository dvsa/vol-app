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
use OlcsTest\Bootstrap;

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
            ->with('lva-licence/variation', ['licence' => $licenceId])
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
        $sm = Bootstrap::getRealServiceManager();

        // Mock the auth service to allow form test to pass through uninhibited
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(false);
        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);

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
            ->with('lva-licence/variation', ['licence' => $licenceId])
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

        $sm->setService('ZfcRbac\Service\AuthorizationService', null);

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
        $sm = Bootstrap::getRealServiceManager();

        // Mock the auth service to allow form test to pass through uninhibited
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(false);
        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);

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
            ->with('lva-licence/variation', ['licence' => $licenceId])
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

        $sm->setService('ZfcRbac\Service\AuthorizationService', null);

    }

    /**
     * Test that address data is repopulated on POST when the fields are locked
     */
    public function testAlterFormDataOnPostEdit()
    {
        $addressData = [
            'id' => 99,
            'version' => 1,
            'operatingCentre' => [
                'id' => 16,
                'version' => 1,
                'address' => [
                    'addressLine1'   => 'DVSA',
                    'addressLine2'   => 'Hillcrest House',
                    'addressLine3'   => '386 Harehills Lane',
                    'addressLine4'   => '',
                    'postcode'       => 'LS9 6NF',
                    'town'           => 'Leeds',
                    'createdOn'      => '2015-02-25T10:41:24+0000',
                    'id'             => 8,
                    'lastModifiedOn' => '2015-02-25T10:41:24+0000',
                    'version'        => 1,
                    'countryCode'    => ['id' => 'GB']
                ]
            ],
        ];

        $this->sm->setService(
            'Entity\LicenceOperatingCentre',
            m::mock()
                ->shouldReceive('getAddressData')
                ->once()
                ->with(99)
                ->andReturn($addressData)
                ->getMock()
        );

        $this->controller->shouldReceive('params')->with('child_id')->andReturn(99);

        $postData = [
            'data' => [
                'id' => '99',
                'version' => '1',
                'noOfVehiclesRequired' => '15',
                'noOfTrailersRequired' => '4',
            ],
            'form-actions'    => ['submit' => ''],
            'operatingCentre' => ['id' => '16', 'version' => '1'],
            'trafficArea'     => 'B',
        ];

        $expectedData = [
           'data' => [
                'id' => '99',
                'version' => '1',
                'noOfVehiclesRequired' => '15',
                'noOfTrailersRequired' => '4',
            ],
            'form-actions'    => ['submit' => ''],
            'operatingCentre' => [
                'id' => '16',
                'version' => '1',
            ],
            'trafficArea'=> 'B',
            'address' => [
                'addressLine1'   => 'DVSA',
                'addressLine2'   => 'Hillcrest House',
                'addressLine3'   => '386 Harehills Lane',
                'addressLine4'   => '',
                'postcode'       => 'LS9 6NF',
                'town'           => 'Leeds',
                'createdOn'      => '2015-02-25T10:41:24+0000',
                'id'             => 8,
                'lastModifiedOn' => '2015-02-25T10:41:24+0000',
                'version'        => 1,
                'countryCode'    => ['id' => 'GB']
            ],
        ];

        $this->assertEquals(
            $expectedData,
            $this->sut->alterFormDataOnPost('edit', $postData, 99)
        );
    }

    public function testAddMessages()
    {
        // Stubbed data
        $licenceId = 4;

        // Mocks
        $mockVariation = m::mock();
        $this->sm->setService('Lva\Variation', $mockVariation);

        // Expectations
        $mockVariation->shouldReceive('addVariationMessage')
            ->with($licenceId);

        $this->sut->addMessages($licenceId);
    }

    public function testAlterFormWithTrafficArea()
    {
        // Stubbed data
        $id = 3;
        $trafficAreaId = 'B';

        $stubbedTolData = [
            'niFlag' => 'N',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];
        $stubbedAddressData = [
            'Results' => [
                [
                    'id' => 1,
                    'operatingCentre' => [
                        'address' => ['id' => 11, 'version' => 1],
                    ],
                ],
                [
                    'id' => 2,
                    'operatingCentre' => [
                        'address' => ['id' => 12, 'version' => 1],
                    ],
                ]
            ],
        ];
        $stubbedTrafficAreaData = [
            'id' => $trafficAreaId,
            'name' => 'Traffic Area B',
        ];
        $stubbedAuths = [
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 5
        ];

        // mock all the things
        $mockLvaAdapter = m::mock();
        $this->sm->setService('licenceLvaAdapter', $mockLvaAdapter);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockLocEntity = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLocEntity);
        $mockLicenceEntity = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $mockTrafficAreaEnforcementAreaEntity = m::mock();
        $this->sm->setService('Entity\TrafficAreaEnforcementArea', $mockTrafficAreaEnforcementAreaEntity);

        $mockForm = m::mock('\Zend\Form\Form');
        $dataTrafficAreaFieldset = m::mock();
        $enforcementAreaField = m::mock();
        $trafficAreaSetField = m::mock();
        $dataFieldset = m::mock();

        // expectations
        $mockLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->andReturnSelf()
            ->shouldReceive('getIdentifier')
            ->andReturn($id)
            ->shouldReceive('alterForm')
            ->once()
            ->with($mockForm)
            ->andReturn($mockForm);

         $mockLicenceEntity
            ->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTolData);

        $mockFormHelper
            ->shouldReceive('removeFieldList')
            ->once()
            ->with(
                $mockForm,
                'data',
                [
                    'totAuthSmallVehicles',
                    'totAuthMediumVehicles',
                    'totAuthLargeVehicles',
                    'totCommunityLicences',
                ]
            );
        $mockFormHelper
            ->shouldReceive('removeValidator')
            ->once()
            ->with($mockForm, 'data->totAuthVehicles', 'Common\Form\Elements\Validators\EqualSum');
        $mockFormHelper
            ->shouldReceive('getValidator')
            ->with($mockForm, 'table->table', 'Common\Form\Elements\Validators\TableRequiredValidator')
            ->andReturn(
                m::mock()
                ->shouldReceive('setMessage')
                ->getMock()
            );
        $mockLocEntity->shouldReceive('getAddressSummaryData')
            ->with($id)
            ->once()
            ->andReturn($stubbedAddressData);

        $mockLicenceEntity
            ->shouldReceive('getTrafficArea')
            ->once()
            ->with($id)
            ->andReturn($stubbedTrafficAreaData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockForm
            ->shouldReceive('get')
            ->with('dataTrafficArea')
            ->andReturn($dataTrafficAreaFieldset);
        $dataTrafficAreaFieldset
            ->shouldReceive('get')
            ->with('enforcementArea')
            ->once()
            ->andReturn($enforcementAreaField);

        $enforcementAreas = ['ENFORCEMENT_AREAS'];
        $mockTrafficAreaEnforcementAreaEntity
            ->shouldReceive('getValueOptions')
            ->once()
            ->with($trafficAreaId)
            ->andReturn($enforcementAreas);

        $enforcementAreaField
            ->shouldReceive('setValueOptions')
            ->once()
            ->with($enforcementAreas);

        $mockFormHelper
            ->shouldReceive('remove')
            ->with($mockForm, 'dataTrafficArea->trafficArea')
            ->once();

        $dataTrafficAreaFieldset
            ->shouldReceive('get')
            ->with('trafficAreaSet')
            ->andReturn($trafficAreaSetField);
        $trafficAreaSetField
            ->shouldReceive('setValue')
            ->once()
            ->with('Traffic Area B')
            ->andReturnSelf()
            ->shouldReceive('setOption');

        $mockForm
            ->shouldReceive('get')
            ->with('data')
            ->andReturn($dataFieldset);
        $dataFieldset
            ->shouldReceive('has')
            ->with('totCommunityLicences')
            ->once()
            ->andReturn(false);

        $mockForm->shouldReceive('getInputFilter')->andReturn($mockForm);
        $dataFieldset->shouldReceive('has')->with('totAuthVehicles')->andReturn(false);
        $dataFieldset->shouldReceive('has')->with('totAuthTrailers')->andReturn(false);

        $mockForm
            ->shouldReceive('has')
            ->with('dataTrafficArea')
            ->once()
            ->andReturn(true);
        $dataTrafficAreaFieldset
            ->shouldReceive('remove')
            ->with('enforcementArea')
            ->once();

        $alteredForm = $this->sut->alterForm($mockForm);

        $this->assertSame($mockForm, $alteredForm);
    }
}
