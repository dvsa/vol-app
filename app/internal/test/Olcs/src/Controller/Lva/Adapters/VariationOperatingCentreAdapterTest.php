<?php

/**
 * Internal Variation Operating Centre Adapter Test
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

    public function testSaveRecord()
    {
        // Stubbed data
        $childId = 'A1';
        $appId = 2;
        $mode = 'edit';
        $formData = ['foo' => 'bar'];
        $formattedData = [
            'applicationOperatingCentre' => [
                'foo' => 'bar',
                'bar' => 'cake'
            ],
            'operatingCentre' => [
                'id' => 5,
                'name' => 'abc'
            ]
        ];
        $tableData = [
            'A1' => [
                'id' => 'A1',
                'action' => 'U'
            ]
        ];
        $stubbedTolData = [
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
        ];
        $expectedSaveData = [
            'foo' => 'bar',
            'bar' => 'cake',
            'operatingCentre' => 5
        ];
        $expectedOcSaveData = [
            'id' => 5,
            'name' => 'abc'
        ];

        // Mocked services
        $mockVariationAdapter = m::mock();
        $this->sm->setService('variationLvaAdapter', $mockVariationAdapter);
        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);
        $mockAocService = m::mock();
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAocService);
        $mockOcService = m::mock();
        $this->sm->setService('Entity\OperatingCentre', $mockOcService);

        // Set expectations
        $this->controller->shouldReceive('params')
            ->with('child_id')
            ->andReturn($childId);

        $mockVariationAdapter
            ->shouldReceive('setController')
            ->with($this->controller)
            ->shouldReceive('getIdentifier')
            ->andReturn($appId);

        $this->sut->shouldReceive('getTableData')
            ->andReturn($tableData);

        $this->sut->shouldReceive('formatCrudDataForSave')
            ->with($formData)
            ->andReturn($formattedData);

        $mockApplicationService->shouldReceive('getTypeOfLicenceData')
            ->with($appId)
            ->andReturn($stubbedTolData);

        $mockAocService->shouldReceive('save')
            ->with($expectedSaveData);

        $mockOcService->shouldReceive('save')
            ->with($expectedOcSaveData);

        // Calling this method should bubble down to call saveRecord
        $this->sut->saveActionFormData($mode, [], $formData);
    }
}
