<?php

/**
 * Variation Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Lva\Traits;

use Common\RefData;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

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
        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockStringHelper = m::mock(StringHelperService::class)->makePartial();
        $this->sut = new Stubs\VariationControllerTraitStub(
            $this->mockNiTextTranslationUtil,
            $this->mockAuthService,
            $this->mockStringHelper
        );
    }

    /**
     * @dataProvider dpGetSectionsForView
     */
    public function testGetSectionsForView(
        $goodsOrPsv,
        $vehicleTypeId,
        $communityLicencesTranslationKey,
        $operatingCentresTranslationKey
    ) {
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
            'operating_centres' => [
                'foo' => '1211'
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
                'alias' => $communityLicencesTranslationKey,
            ],
            'operating_centres' => [
                'foo' => '1211',
                'class' => 'incomplete',
                'route' => 'lva-variation/operating_centres',
                'alias' => $operatingCentresTranslationKey,
            ]
        ];

        $applicationData = [
            'applicationCompletion' => [
                'typeOfLicenceStatus' => RefData::VARIATION_STATUS_UPDATED,
                'businessTypeStatus' => RefData::VARIATION_STATUS_UNCHANGED,
                'businessDetailsStatus' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
                'communityLicencesStatus' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
                'operatingCentresStatus' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
            ],
            'status' => [
                'id' => 'XXX'
            ],
            'goodsOrPsv' => [
                'id' => $goodsOrPsv
            ],
            'vehicleType' => [
                'id' => $vehicleTypeId
            ],
        ];

        $this->setupGetApplicationData($applicationData);

        // Setup
        $this->sut->setApplicationId($id);
        $this->sut->setAccessibleSections($accessibleSections);

        $response = $this->sut->callGetSectionsForView();

        $this->assertEquals($expected, $response);
    }

    public function dpGetSectionsForView()
    {
        return [
            [
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::APP_VEHICLE_TYPE_HGV,
                'community_licences',
                'operating_centres',
            ],
            [
                RefData::LICENCE_CATEGORY_PSV,
                RefData::APP_VEHICLE_TYPE_LGV,
                'community_licences.psv',
                'operating_centres.lgv',
            ],
        ];
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
                'id' => RefData::APPLICATION_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
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
