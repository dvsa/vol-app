<?php

/**
 * Variation Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Variation Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationControllerTraitTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp(): void
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new Stubs\VariationControllerTraitStub();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetSectionsForView()
    {
        // Params
        $id = 3;
        $accessibleSections = [
            'type_of_licence' => [
                'foo' => '123'
            ],
            'business_type' => [
                'foo' => '456'
            ],
            'business_details' => [
                'foo' => '789'
            ],
            'community_licences' => [
                'foo' => '1011'
            ],
        ];

        $expected = [
            'overview' => [
                'class' => 'no-background',
                'route' => 'lva-variation'
            ],
            'type_of_licence' => [
                'foo' => '123',
                'class' => 'edited',
                'route' => 'lva-variation/type_of_licence',
                'alias' => 'type_of_licence',
            ],
            'business_type' => [
                'foo' => '456',
                'class' => '',
                'route' => 'lva-variation/business_type',
                'alias' => 'business_type',
            ],
            'business_details' => [
                'foo' => '789',
                'class' => 'incomplete',
                'route' => 'lva-variation/business_details',
                'alias' => 'business_details',
            ],
            'community_licences' => [
                'foo' => '1011',
                'class' => 'incomplete',
                'route' => 'lva-variation/community_licences',
                'alias' => 'community_licences.psv',
            ]
        ];

        $applicationData = [
            'applicationCompletion' => [
                'typeOfLicenceStatus' => RefData::VARIATION_STATUS_UPDATED,
                'businessTypeStatus' => RefData::VARIATION_STATUS_UNCHANGED,
                'businessDetailsStatus' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
                'communityLicencesStatus' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
            ],
            'status' => [
                'id' => 'XXX'
            ]
        ];

        $this->setupGetApplicationData($applicationData);

        // Setup
        $this->sut->setApplicationId($id);
        $this->sut->setAccessibleSections($accessibleSections);

        $response = $this->sut->callGetSectionsForView();

        $this->assertEquals($expected, $response);
    }

    public function testGetSectionsForViewValidStatus()
    {
        // Params
        $id = 3;
        $accessibleSections = [
            'type_of_licence' => [
                'foo' => '123'
            ],
            'business_type' => [
                'foo' => '456'
            ],
            'business_details' => [
                'foo' => '789'
            ],
        ];

        $expected = [
            'overview' => [
                'class' => 'no-background',
                'route' => 'lva-variation'
            ],
        ];

        $applicationData = [
            'applicationCompletion' => [
                'typeOfLicenceStatus' => RefData::VARIATION_STATUS_UPDATED,
                'businessTypeStatus' => RefData::VARIATION_STATUS_UNCHANGED,
                'businessDetailsStatus' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
            ],
            'status' => [
                'id' => \Common\RefData::APPLICATION_STATUS_VALID
            ]
        ];

        $this->setupGetApplicationData($applicationData);

        // Setup
        $this->sut->setApplicationId($id);
        $this->sut->setAccessibleSections($accessibleSections);

        $response = $this->sut->callGetSectionsForView();

        $this->assertEquals($expected, $response);
    }

    /**
     * Setup a mock to handle getting application Data
     *
     * @param array $applicationData
     */
    protected function setupGetApplicationData($applicationData)
    {
        $mockResponse = m::mock();
        $mockResponse->shouldReceive('isNotFound')->andReturn(false);
        $mockResponse->shouldReceive('isOk')->andReturn(true);
        $mockResponse->shouldReceive('getResult')->andReturn($applicationData);

        $mockPluginManager = m::mock(\Laminas\Mvc\Controller\PluginManager::class)->makePartial();
        $this->sut->setPluginManager($mockPluginManager);
        $mockPluginManager->shouldReceive('get')->with('handleQuery', null)->andReturn($mockResponse);
    }
}
