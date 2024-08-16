<?php

/**
 * Application Operating Centres Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationGoodsOcTotalAuthReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationOperatingCentresReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationPsvOcTotalAuthReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\GoodsOperatingCentreReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\PsvOperatingCentreReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\TrafficAreaReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Operating Centres Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentresReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var PsvOperatingCentreReviewService */
    private $psvOperatingCentreReviewService;

    /** @var ApplicationPsvOcTotalAuthReviewService */
    private $applicationPsvOcTotalAuthReviewService;

    /** @var GoodsOperatingCentreReviewService */
    private $goodsOperatingCentreReviewService;

    /** @var ApplicationGoodsOcTotalAuthReviewService */
    private $applicationGoodsOcTotalAuthReviewService;

    /** @var TrafficAreaReviewService */
    private $trafficAreaReviewService;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->psvOperatingCentreReviewService = m::mock(PsvOperatingCentreReviewService::class);

        $this->applicationPsvOcTotalAuthReviewService = m::mock(ApplicationPsvOcTotalAuthReviewService::class);

        $this->goodsOperatingCentreReviewService = m::mock(GoodsOperatingCentreReviewService::class);

        $this->applicationGoodsOcTotalAuthReviewService = m::mock(ApplicationGoodsOcTotalAuthReviewService::class);

        $this->trafficAreaReviewService = m::mock(TrafficAreaReviewService::class);

        $this->sut = new ApplicationOperatingCentresReviewService(
            $abstractReviewServiceServices,
            $this->psvOperatingCentreReviewService,
            $this->applicationPsvOcTotalAuthReviewService,
            $this->goodsOperatingCentreReviewService,
            $this->applicationGoodsOcTotalAuthReviewService,
            $this->trafficAreaReviewService
        );
    }

    /**
     * @dataProvider psvProvider
     */
    public function testGetConfigFromDataWithEmptyOcList($isGoods, $expectedOcServiceProperty, $expectedTaServiceProperty)
    {
        $expectedOcService = $this->{$expectedOcServiceProperty};
        $expectedTaService = $this->{$expectedTaServiceProperty};

        $data = [
            'isGoods' => $isGoods,
            'operatingCentres' => []
        ];
        $expected = [
            'subSections' => [
                [
                    'mainItems' => [
                        'TACONFIG',
                        'TOTAL_AUTH_CONFIG'
                    ]
                ]
            ]
        ];

        // Expectations
        $this->trafficAreaReviewService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('TACONFIG');

        $expectedTaService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('TOTAL_AUTH_CONFIG');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    /**
     * @dataProvider psvProvider
     */
    public function testGetConfigFromDataWithOcList($isGoods, $expectedOcServiceProperty, $expectedTaServiceProperty)
    {
        $expectedOcService = $this->{$expectedOcServiceProperty};
        $expectedTaService = $this->{$expectedTaServiceProperty};

        $data = [
            'isGoods' => $isGoods,
            'operatingCentres' => [
                ['foo' => 'bar'],
                ['foo' => 'cake']
            ]
        ];
        $expected = [
            'subSections' => [
                [
                    'mainItems' => [
                        'foobar',
                        'foocake'
                    ],
                ],
                [
                    'mainItems' => [
                        'TACONFIG',
                        'TOTAL_AUTH_CONFIG'
                    ]
                ]
            ]
        ];

        // Expectations
        $this->trafficAreaReviewService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('TACONFIG');

        $expectedTaService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('TOTAL_AUTH_CONFIG');

        $expectedOcService->shouldReceive('getConfigFromData')
            ->with(['foo' => 'bar'])
            ->andReturn('foobar')
            ->shouldReceive('getConfigFromData')
            ->with(['foo' => 'cake'])
            ->andReturn('foocake');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public function psvProvider()
    {
        return [
            [
                true,
                'goodsOperatingCentreReviewService',
                'applicationGoodsOcTotalAuthReviewService'
            ],
            [
                false,
                'psvOperatingCentreReviewService',
                'applicationPsvOcTotalAuthReviewService'
            ]
        ];
    }

    /**
     * @dataProvider dpGetHeaderTranslationKey
     */
    public function testGetHeaderTranslationKey($vehicleTypeId, $expectedTranslationKey)
    {
        $reviewData = [
            'vehicleType' => [
                'id' => $vehicleTypeId
            ]
        ];

        $this->assertEquals(
            $expectedTranslationKey,
            $this->sut->getHeaderTranslationKey($reviewData, 'section-key')
        );
    }

    public function dpGetHeaderTranslationKey()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_PSV, 'review-section-key'],
            [RefData::APP_VEHICLE_TYPE_HGV, 'review-section-key'],
            [RefData::APP_VEHICLE_TYPE_MIXED, 'review-section-key'],
            [RefData::APP_VEHICLE_TYPE_LGV, 'review-authorisation'],
        ];
    }
}
