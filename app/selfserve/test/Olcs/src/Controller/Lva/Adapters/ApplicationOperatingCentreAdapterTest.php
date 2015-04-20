<?php

/**
 * External Application Operating Centre Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationOperatingCentreAdapter;
use Common\Service\Entity\LicenceEntityService;
use OlcsTest\Bootstrap;

/**
 * Application Operating Centre Adapter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentreAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $controller;
    protected $sm;

    public function setUp()
    {
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new ApplicationOperatingCentreAdapter();
        $this->sut->setController($this->controller);
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterForm()
    {
        // Stubbed data
        $id = 3;
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

        $form->get('table')->get('table')->setTable(
            m::mock()
                ->shouldReceive('setFieldset')
                ->shouldReceive('removeColumn')
                ->shouldReceive('setDisabled')
                ->getMock()
        );

        // Mocked services
        $mockLvaAdapter = m::mock();
        $this->sm->setService('applicationLvaAdapter', $mockLvaAdapter);
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $mockAocEntity = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocEntity);
        $mockValidator = m::mock('Zend\Validator\ValidatorInterface');
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        // Expectations
        $mockLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('alterForm')
            ->with($form)
            ->shouldReceive('getIdentifier')
            ->andReturn($id);

        $mockApplicationEntity->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTolData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockAocEntity->shouldReceive('getAddressSummaryData')
            ->with($id)
            ->andReturn($stubbedAddressData);

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

        $alteredForm = $this->sut->alterForm($form);

        $this->assertFalse($alteredForm->get('data')->has('totCommunityLicences'));

        $sm->setService('ZfcRbac\Service\AuthorizationService', null);

    }

    public function testAlterFormWithCommunityLicences()
    {
        // Stubbed data
        $id = 3;
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
        $mockLvaAdapter = m::mock();
        $this->sm->setService('applicationLvaAdapter', $mockLvaAdapter);
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $mockAocEntity = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocEntity);
        $mockValidator = m::mock('Zend\Validator\ValidatorInterface');
        $this->sm->setService('CantIncreaseValidator', $mockValidator);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        // Expectations
        $mockLvaAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('alterForm')
            ->with($form)
            ->shouldReceive('getIdentifier')
            ->andReturn($id);

        $mockApplicationEntity->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTolData)
            ->shouldReceive('getTotalAuths')
            ->andReturn($stubbedAuths);

        $mockAocEntity->shouldReceive('getAddressSummaryData')
            ->with($id)
            ->andReturn($stubbedAddressData);

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

        $mockViewRenderer->shouldReceive('render')
            ->andReturn('-LOCKED');

        $alteredForm = $this->sut->alterForm($form);

        $this->assertTrue($alteredForm->get('data')->has('totCommunityLicences'));
        $label = $alteredForm->get('data')->get('totCommunityLicences')->getLabel();

        $this->assertEquals(
            'application_operating-centres_authorisation.data.totCommunityLicences-external-app',
            $label
        );
        $sm->setService('ZfcRbac\Service\AuthorizationService', null);

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

        // mock all the things
        $mockLvaAdapter = m::mock();
        $this->sm->setService('applicationLvaAdapter', $mockLvaAdapter);
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockAocEntity = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocEntity);
        $mockLicenceLvaAdapter = m::mock();
        $this->sm->setService('LicenceLvaAdapter', $mockLicenceLvaAdapter);
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

         $mockApplicationEntity
            ->shouldReceive('getTypeOfLicenceData')
            // ->once() // this gets called multiple times :-/
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
            ->once()
            ->andReturn($dataFieldset);
        $dataFieldset
            ->shouldReceive('has')
            ->with('totCommunityLicences')
            ->once()
            ->andReturn(false);

        $mockForm
            ->shouldReceive('has')
            ->with('dataTrafficArea')
            ->once()
            ->andReturn(true);

        $mockForm->shouldReceive('get')
            ->with('table')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('table')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getTable')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('removeColumn')
                                    ->with('noOfComplaints')
                                    ->getMock()
                            )->getMock()
                    )->getMock()
            );

        $dataTrafficAreaFieldset
            ->shouldReceive('remove')
            ->with('enforcementArea')
            ->once();

        $alteredForm = $this->sut->alterForm($mockForm);

        $this->assertSame($mockForm, $alteredForm);
    }
}
