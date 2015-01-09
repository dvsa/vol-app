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
}
