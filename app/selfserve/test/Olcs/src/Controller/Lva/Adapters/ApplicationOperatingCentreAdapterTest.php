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
}
