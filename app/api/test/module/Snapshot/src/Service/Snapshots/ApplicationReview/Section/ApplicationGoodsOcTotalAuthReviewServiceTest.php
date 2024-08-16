<?php

/**
 * Application Goods Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationGoodsOcTotalAuthReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Goods Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationGoodsOcTotalAuthReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationGoodsOcTotalAuthReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromDataWithHgv()
    {
        $data = [
            'totAuthVehicles' => 25,
            'totAuthLgvVehicles' => null,
            'totAuthTrailers' => 200,
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV
            ]
        ];

        $expected = [
            'header' => 'review-operating-centres-authorisation-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-authorisation-vehicles',
                        'value' => 25
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-trailers',
                        'value' => 200
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public function testGetConfigFromDataWithMixedAndNullLgvAuth()
    {
        $data = [
            'totAuthHgvVehicles' => 50,
            'totAuthVehicles' => 50,
            'totAuthLgvVehicles' => null,
            'totAuthTrailers' => 12,
            'totCommunityLicences' => 200,
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_MIXED
            ]
        ];

        $expected = [
            'header' => 'review-operating-centres-authorisation-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-authorisation-vehicles',
                        'value' => 50
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-trailers',
                        'value' => 12
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-community-licences',
                        'value' => 200
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    /**
     * @dataProvider dpGetConfigFromDataWithMixedAndNumericLgvAuth
     */
    public function testGetConfigFromDataWithMixedAndNumericLgvAuth($totAuthLgvVehicles)
    {
        $data = [
            'totAuthHgvVehicles' => 50,
            'totAuthLgvVehicles' => $totAuthLgvVehicles,
            'totAuthTrailers' => 12,
            'totCommunityLicences' => 200,
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_MIXED
            ]
        ];

        $expected = [
            'header' => 'review-operating-centres-authorisation-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-authorisation-heavy-goods-vehicles',
                        'value' => 50
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-light-goods-vehicles',
                        'value' => $totAuthLgvVehicles
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-trailers',
                        'value' => 12
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-community-licences',
                        'value' => 200
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public function dpGetConfigFromDataWithMixedAndNumericLgvAuth()
    {
        return [
            [0],
            [5],
        ];
    }

    public function testGetConfigFromDataWithLgv()
    {
        $data = [
            'totAuthLgvVehicles' => 25,
            'totCommunityLicences' => 200,
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_LGV
            ]
        ];

        $expected = [
            'header' => 'review-operating-centres-authorisation-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-authorisation-light-goods-vehicles',
                        'value' => 25
                    ],
                    [
                        'label' => 'review-operating-centres-authorisation-community-licences',
                        'value' => 200
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
