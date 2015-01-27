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

        // Don't like mocking the SUT, but mocking the extreemly deep abstract methods is less evil
        // than writing extreemly tightly coupled tests with tonnes of mocked dependencies
        $this->sut = m::mock('Olcs\Controller\Lva\Adapters\VariationOperatingCentreAdapter')
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $this->sut->setController($this->controller);
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterActionFormWithNewGoods()
    {
        // Stubbed data
        $childId = null;
        $applicationId = 4;
        $stubbedTolData = array(
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        );

        // Mock dependencies
        $mockForm = m::mock('\Zend\Form\Form');

        // Mock services
        $mockLvaAdapter = m::mock();
        $this->sm->setService('VariationLvaAdapter', $mockLvaAdapter);
        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        // Expectations
        $mockLvaAdapter->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('getIdentifier')
            ->andReturn($applicationId);

        $mockApplicationService->shouldReceive('getTypeOfLicenceData')
            ->with($applicationId)
            ->andReturn($stubbedTolData);

        $this->controller->shouldReceive('params')
            ->with('child_id')
            ->andReturn($childId);

        $this->assertSame($mockForm, $this->sut->alterActionForm($mockForm));
    }

    public function testAlterActionFormWithExistingGoods()
    {
        // Stubbed data
        $childId = 'L1';
        $applicationId = 4;
        $stubbedTolData = array(
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        );
        $stubbedTableData = array(
            'L1' => array(
                'id' => 'L1',
                'action' => 'E'
            )
        );
        $stubbedAuthValues = array(123, 456);

        // Mock dependencies
        $mockForm = m::mock('\Zend\Form\Form');

        // Setup mocks
        $mockInputFilter = m::mock();
        $mockAddressElement = m::mock();
        $mockAddressFilter = m::mock();

        // Mock services
        $mockLvaAdapter = m::mock();
        $this->sm->setService('VariationLvaAdapter', $mockLvaAdapter);
        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockLvaAdapter->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('getIdentifier')
            ->andReturn($applicationId);

        $mockApplicationService->shouldReceive('getTypeOfLicenceData')
            ->with($applicationId)
            ->andReturn($stubbedTolData);

        $this->controller->shouldReceive('params')
            ->with('child_id')
            ->andReturn($childId);

        $this->sut->shouldReceive('getTableData')
            ->andReturn($stubbedTableData);

        $this->sut->shouldReceive('getCurrentAuthorisationValues')
            ->andReturn($stubbedAuthValues);

        $mockForm->shouldReceive('get')
            ->with('data')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('noOfVehiclesRequired')
            ->andReturn(
                m::mock()
                ->shouldReceive('setAttribute')
                ->with('data-current', 123)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('noOfTrailersRequired')
            ->andReturn(
                m::mock()
                ->shouldReceive('setAttribute')
                ->with('data-current', 456)
                ->getMock()
            )->shouldReceive('get')
            ->with('address')
            ->andReturn($mockAddressElement)
            ->shouldReceive('getInputFilter')
            ->andReturn($mockInputFilter);

        $mockInputFilter->shouldReceive('get')
            ->with('address')
            ->andReturn($mockAddressFilter);

        $mockAddressElement->shouldReceive('remove')
            ->with('searchPostcode');

        $mockFormHelper->shouldReceive('disableElements')
            ->with($mockAddressElement);

        $mockFormHelper->shouldReceive('disableValidation')
            ->with($mockAddressFilter);

        $this->assertSame($mockForm, $this->sut->alterActionForm($mockForm));
    }

    public function testProcessAddressLookupForm()
    {
        // Stubbed data
        $childId = 'L1';
        $stubbedTableData = array(
            'L1' => array(
                'id' => 'L1',
                'action' => 'E'
            )
        );

        // Mocked dependencies
        $mockForm = m::mock();
        $mockRequest = m::mock();

        $this->controller->shouldReceive('params')
            ->with('child_id')
            ->andReturn($childId);

        $this->sut->shouldReceive('getTableData')
            ->andReturn($stubbedTableData);

        $this->assertFalse($this->sut->processAddressLookupForm($mockForm, $mockRequest));
    }

    public function testProcessAddressLookupFormWithAdd()
    {
        // Stubbed data
        $childId = null;

        // Mocked dependencies
        $mockForm = m::mock();
        $mockRequest = m::mock();

        // Mock services
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->controller->shouldReceive('params')
            ->with('child_id')
            ->andReturn($childId);

        $mockFormHelper->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(true);

        $this->assertTrue($this->sut->processAddressLookupForm($mockForm, $mockRequest));
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
        $sm = \OlcsTest\Bootstrap::getServiceManager();
        $form = $sm->get('Helper\Form')->createForm('Lva\OperatingCentres');
        // As it's a component test, we will be better off not mocking the form helper
        $this->sm->setService('Helper\Form', $sm->get('Helper\Form'));

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
        $sm = \OlcsTest\Bootstrap::getServiceManager();
        $form = $sm->get('Helper\Form')->createForm('Lva\OperatingCentres');
        // As it's a component test, we will be better off not mocking the form helper
        $this->sm->setService('Helper\Form', $sm->get('Helper\Form'));
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
                ->shouldReceive('getOperatingCentresForLicence')
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
                    'Results' => [ // N.B. different structure
                        [
                            'operatingCentre' => ['id' => 16],
                            'noOfVehiclesRequired' => 10,
                            'noOfTrailersRequired' => 10,
                        ],
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
                    'Results' => [ // N.B. different structure
                        [
                            'operatingCentre' => ['id' => 16],
                            'noOfVehiclesRequired' => 10,
                            'noOfTrailersRequired' => 10,
                        ],
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
                    'Results' => [
                        [
                            'operatingCentre' => ['id' => 16],
                            'noOfVehiclesRequired' => 10,
                            'noOfTrailersRequired' => 10,
                        ],
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
                    'Results' => [
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
                    'Results' => [
                        [
                            'operatingCentre' => ['id' => 16],
                            'noOfVehiclesRequired' => 10,
                            'noOfTrailersRequired' => 10,
                        ],
                    ],
                ],
                'maybeCreateVariationFee'
            ],
            'no changes to OCs' => [ // remove any outstanding fee
                [],
                [
                    'Results' => [
                        [
                            'operatingCentre' => ['id' => 16],
                            'noOfVehiclesRequired' => 10,
                            'noOfTrailersRequired' => 10,
                        ],
                    ],
                ],
                'maybeCancelVariationFee'
            ],
        ];
    }
}
