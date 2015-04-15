<?php

/**
 * External Variation Operating Centre Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Entity\LicenceEntityService;
use OlcsTest\Bootstrap;

/**
 * Variation Operating Centre Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $controller;
    protected $sm;

    public function setUp()
    {
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        // Don't like mocking the SUT, but mocking the extremely deep abstract methods is less evil
        // than writing extremely tightly coupled tests with tonnes of mocked dependencies
        $this->sut = m::mock('Olcs\Controller\Lva\Adapters\VariationOperatingCentreAdapter')
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $this->sut->setController($this->controller);
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterForm()
    {
        // Stubbed data
        $id = 3;
        $licenceId = 6;
        $stubbedTolData = [
            'niFlag' => 'Y',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];
        $stubbedAddressData = [
            'Results' => []
        ];
        $stubbedLicenceAddressData = [
            'Results' => []
        ];
        $stubbedAuths = [
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 5
        ];
        $stubbedLicData = [
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

        $form->get('table')->get('table')->setTable(
            m::mock()
                ->shouldReceive('setFieldset')
                ->shouldReceive('removeColumn')
                ->shouldReceive('setDisabled')
                ->getMock()
        );

        // Mocked services
        $mockVariationLvaAdapter = m::mock();
        $this->sm->setService('variationLvaAdapter', $mockVariationLvaAdapter);
        $mockLicenceLvaAdapter = m::mock();
        $this->sm->setService('licenceLvaAdapter', $mockLicenceLvaAdapter);
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $mockLicenceEntity = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $mockAocEntity = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocEntity);
        $mockLocEntity = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLocEntity);
        $mockValidator = m::mock('Zend\Validator\ValidatorInterface');
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        // Expectations
        $mockVariationLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('alterForm')
            ->with($form)
            ->shouldReceive('getIdentifier')
            ->andReturn($id);

        $mockLicenceLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $mockApplicationEntity->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTolData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockAocEntity->shouldReceive('getAddressSummaryData')
            ->with($id)
            ->andReturn($stubbedAddressData);

        $mockLocEntity->shouldReceive('getAddressSummaryData')
            ->with($licenceId)
            ->andReturn($stubbedLicenceAddressData);

        $this->controller->shouldReceive('url->fromRoute')
            ->with('create_variation', ['licence' => $id])
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

        $mockLicenceEntity->shouldReceive('getById')
            ->with($licenceId)
            ->andReturn($stubbedLicData);

        $mockTranslator->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [10])
            ->andReturn('HINT 10')
            ->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [5])
            ->andReturn('HINT 5');

        $alteredForm = $this->sut->alterForm($form);

        $this->assertFalse($alteredForm->get('data')->has('totCommunityLicences'));

        $sm->setService('ZfcRbac\Service\AuthorizationService', null);

    }

    public function testAlterFormWithCommunityLicences()
    {
        // Stubbed data
        $id = 3;
        $licenceId = 6;
        $stubbedTolData = [
            'niFlag' => 'Y',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];
        $stubbedAddressData = [
            'Results' => []
        ];
        $stubbedLicenceAddressData = [
            'Results' => []
        ];
        $stubbedAuths = [
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 5
        ];
        $stubbedLicData = [
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

        $form->get('table')->get('table')->setTable(
            m::mock()
                ->shouldReceive('setFieldset')
                ->shouldReceive('removeColumn')
                ->shouldReceive('setDisabled')
                ->getMock()
        );

        $sm->setAllowOverride(true);
        $mockViewRenderer = m::mock();
        $sm->setService('ViewRenderer', $mockViewRenderer);

        // Mocked services
        $mockVariationLvaAdapter = m::mock();
        $this->sm->setService('variationLvaAdapter', $mockVariationLvaAdapter);
        $mockLicenceLvaAdapter = m::mock();
        $this->sm->setService('licenceLvaAdapter', $mockLicenceLvaAdapter);
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $mockLicenceEntity = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $mockAocEntity = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocEntity);
        $mockLocEntity = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLocEntity);
        $mockValidator = m::mock('Zend\Validator\ValidatorInterface');
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        // Expectations
        $mockVariationLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('alterForm')
            ->with($form)
            ->shouldReceive('getIdentifier')
            ->andReturn($id);

        $mockLicenceLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);

        $mockApplicationEntity->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTolData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockAocEntity->shouldReceive('getAddressSummaryData')
            ->with($id)
            ->andReturn($stubbedAddressData);

        $mockLocEntity->shouldReceive('getAddressSummaryData')
            ->with($licenceId)
            ->andReturn($stubbedLicenceAddressData);

        $this->controller->shouldReceive('url->fromRoute')
            ->with('create_variation', ['licence' => $id])
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

        $mockLicenceEntity->shouldReceive('getById')
            ->with($licenceId)
            ->andReturn($stubbedLicData);

        $mockTranslator->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [10])
            ->andReturn('HINT 10')
            ->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [5])
            ->andReturn('HINT 5');

        $mockViewRenderer->shouldReceive('render')
            ->andReturn('-LOCKED');

        $alteredForm = $this->sut->alterForm($form);

        $this->assertTrue($alteredForm->get('data')->has('totCommunityLicences'));
        $label = $alteredForm->get('data')->get('totCommunityLicences')->getLabel();

        $this->assertEquals('application_operating-centres_authorisation.data.totCommunityLicences-LOCKED', $label);

        $sm->setService('ZfcRbac\Service\AuthorizationService', null);

    }

    /**
     * @dataProvider handleFeesProvider
     *
     * @param array $applicationOcs Application Operating Centres
     * @param array $licenceOcs Licence Operating Centres
     * @param string $expectedMethod expect call to this method on @see ApplicationProcessingEntityService
     */
    public function testHandleFees($applicationOcs, $licenceOcs, $expectedMethod)
    {
        $applicationId = '123';
        $licenceId     = '456';

        switch ($expectedMethod) {
            case 'maybeCreateVariationFee':
                $expectedArgs = [$applicationId, $licenceId];
                break;
            case 'maybeCancelVariationFee':
                $expectedArgs = [$applicationId];
                break;
            default:
                $expectedArgs = [];
                break;
        }
        $this->sm->setService(
            'Processing\Application',
            m::mock()
                ->shouldReceive($expectedMethod)
                ->once()
                ->withArgs($expectedArgs)
                ->getMock()
        );

        $this->sm->setService(
            'VariationLvaAdapter',
            m::mock()
                ->shouldReceive('getIdentifier')
                    ->andReturn($applicationId)
                ->shouldReceive('setController')
                    ->with($this->controller)
                ->getMock()
        );
        $this->sm->setService(
            'LicenceLvaAdapter',
            m::mock()
                ->shouldReceive('getIdentifier')
                    ->andReturn($licenceId)
                ->shouldReceive('setController')
                    ->with($this->controller)
                ->getMock()
        );

        $this->sm->setService(
            'Entity\ApplicationOperatingCentre',
            m::mock()
                ->shouldReceive('getForApplication')
                ->once()
                ->with($applicationId)
                ->andReturn($applicationOcs)
                ->getMock()
        );
        $this->sm->setService(
            'Entity\LicenceOperatingCentre',
            m::mock()
                ->shouldReceive('getAuthorityDataForLicence')
                ->once()
                ->with($licenceId)
                ->andReturn($licenceOcs)
                ->getMock()
        );

        $this->sut->handleFees();
    }

    public function handleFeesProvider()
    {
        return [
            'one OC with vehicle increase' => [
                [
                    [
                        'action' => 'U',
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 12,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                [
                    [
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                'maybeCreateVariationFee'
            ],
            'one OC with trailer increase' => [
                [
                    [
                        'action' => 'U',
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 12,
                    ],
                ],
                [
                    [
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                'maybeCreateVariationFee'
            ],
            'one OC with no increase' => [
                [
                    [
                        'action' => 'U',
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                [
                    [
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                'maybeCancelVariationFee'
            ],
            'two OCs, one with increase' => [
                [
                    [
                        'action' => 'U',
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 12,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                [
                    [
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                    [
                        'operatingCentre' => ['id' => 17],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                'maybeCreateVariationFee'
            ],
            'add an OC' => [
                [
                    [
                        'action' => 'A',
                        'operatingCentre' => ['id' => 73],
                        'noOfVehiclesRequired' => 2,
                        'noOfTrailersRequired' => 2,
                    ],
                ],
                [
                    [
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                'maybeCreateVariationFee'
            ],
            'no changes to OCs' => [ // remove any outstanding fee
                [],
                [
                    [
                        'operatingCentre' => ['id' => 16],
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 10,
                    ],
                ],
                'maybeCancelVariationFee'
            ],
        ];
    }

    public function testAlterFormDataOnPostOnAdd()
    {
        $mode = 'add';
        $data = [
            'foo' => 'bar'
        ];
        $childId = 123;

        $this->assertEquals($data, $this->sut->alterFormDataOnPost($mode, $data, $childId));
    }

    public function testAlterFormDataOnPostOnEdit()
    {
        $mode = 'edit';
        $data = [
            'foo' => 'bar'
        ];
        $addressData = [
            'operatingCentre' => [
                'address' => [
                    'addressLine1' => '123 Street'
                ]
            ]
        ];
        $expectedData = [
            'foo' => 'bar',
            'address' => [
                'addressLine1' => '123 Street'
            ]
        ];
        $childId = 123;

        $this->sut->shouldReceive('getAddressData')
            ->with(123)
            ->andReturn($addressData);

        $this->assertEquals($expectedData, $this->sut->alterFormDataOnPost($mode, $data, $childId));
    }

    public function testAlterFormWithTrafficArea()
    {
        // Stubbed data
        $id = 3;
        $licenceId = 77;
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
                        'id' => 5,
                        'address' => ['id' => 11, 'version' => 1],
                    ],
                ],
                [
                    'id' => 2,
                    'operatingCentre' => [
                        'id' => 6,
                        'address' => ['id' => 12, 'version' => 1],
                    ],
                ]
            ],
        ];
        $stubbedLicenceAddressData = [
            'Results' => []
        ];

        $stubbedTrafficAreaData = [
            'id' => $trafficAreaId,
            'name' => 'Traffic Area B',
        ];

        $stubbedLicData = [
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 5
        ];

        // mock all the things
        $mockLvaAdapter = m::mock();
        $this->sm->setService('variationLvaAdapter', $mockLvaAdapter);
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockAocEntity = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocEntity);
        $mockLocEntity = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLocEntity);
        $mockLicenceLvaAdapter = m::mock();
        $this->sm->setService('LicenceLvaAdapter', $mockLicenceLvaAdapter);
        $mockLicenceEntity = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $mockTrafficAreaEnforcementAreaEntity = m::mock();
        $this->sm->setService('Entity\TrafficAreaEnforcementArea', $mockTrafficAreaEnforcementAreaEntity);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

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

         $mockApplicationEntity
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
        $mockAocEntity->shouldReceive('getAddressSummaryData')
            ->with($id)
            ->once()
            ->andReturn($stubbedAddressData);
        $mockLocEntity->shouldReceive('getAddressSummaryData')
            ->with($licenceId)
            ->andReturn($stubbedLicenceAddressData);

        $mockLicenceLvaAdapter
            ->shouldReceive('setController')
            ->once()
            ->with($this->controller)
            ->andReturnSelf()
            ->shouldReceive('getIdentifier')
            ->andReturn($licenceId);
        $mockLicenceEntity
            ->shouldReceive('getTrafficArea')
            ->once()
            ->with($licenceId)
            ->andReturn($stubbedTrafficAreaData);
        $mockLicenceEntity->shouldReceive('getById')
            ->with($licenceId)
            ->andReturn($stubbedLicData);

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
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthVehicles')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthTrailers')
            ->andReturn(false);

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
