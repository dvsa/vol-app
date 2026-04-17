<?php

declare(strict_types=1);

/**
 * Variation Operating Centres Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\GoodsOperatingCentreReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\PsvOperatingCentreReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationGoodsOcTotalAuthReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationOperatingCentresReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationPsvOcTotalAuthReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Operating Centres Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentresReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    /** @var PsvOperatingCentreReviewService */
    private $psvOperatingCentreReviewService;

    /** @var VariationPsvOcTotalAuthReviewService */
    private $variationPsvOcTotalAuthReviewService;

    /** @var GoodsOperatingCentreReviewService */
    private $goodsOperatingCentreReviewService;

    /** @var VariationGoodsOcTotalAuthReviewService */
    private $variationGoodsOcTotalAuthReviewService;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->psvOperatingCentreReviewService = m::mock(PsvOperatingCentreReviewService::class);

        $this->variationPsvOcTotalAuthReviewService = m::mock(VariationPsvOcTotalAuthReviewService::class);

        $this->goodsOperatingCentreReviewService = m::mock(GoodsOperatingCentreReviewService::class);

        $this->variationGoodsOcTotalAuthReviewService = m::mock(VariationGoodsOcTotalAuthReviewService::class);

        $this->sut = new VariationOperatingCentresReviewService(
            $abstractReviewServiceServices,
            $this->psvOperatingCentreReviewService,
            $this->variationPsvOcTotalAuthReviewService,
            $this->goodsOperatingCentreReviewService,
            $this->variationGoodsOcTotalAuthReviewService
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('psvProvider')]
    public function testGetConfigFromDataWithEmptyOcList(
        mixed $isGoods,
        mixed $expectedOcServiceProperty,
        mixed $expectedTaServiceProperty
    ): void {
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
                        'TOTAL_AUTH_CONFIG'
                    ]
                ]
            ]
        ];

        // Expectations
        $expectedTaService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('TOTAL_AUTH_CONFIG');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('psvProvider')]
    public function testGetConfigFromDataWithOcList(
        mixed $isGoods,
        mixed $expectedOcServiceProperty,
        mixed $expectedTaServiceProperty
    ): void {
        $expectedOcService = $this->{$expectedOcServiceProperty};
        $expectedTaService = $this->{$expectedTaServiceProperty};

        $data = [
            'isGoods' => $isGoods,
            'operatingCentres' => [
                [
                    'action' => 'A',
                    'foo' => 'bar'
                ],
                [
                    'action' => 'A',
                    'foo' => 'bar1'
                ],
                [
                    'action' => 'U',
                    'foo' => 'cake'
                ],
                [
                    'action' => 'D',
                    'foo' => 'blah'
                ]
            ]
        ];
        $expected = [
            'subSections' => [
                [
                    'title' => 'variation-review-operating-centres-added-title',
                    'mainItems' => [
                        'foobar',
                        'foobar1'
                    ],
                ],
                [
                    'title' => 'variation-review-operating-centres-updated-title',
                    'mainItems' => [
                        'foocake'
                    ],
                ],
                [
                    'title' => 'variation-review-operating-centres-deleted-title',
                    'mainItems' => [
                        'fooblah'
                    ],
                ],
                [
                    'mainItems' => [
                        'TOTAL_AUTH_CONFIG'
                    ]
                ]
            ]
        ];

        // Expectations
        $expectedTaService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('TOTAL_AUTH_CONFIG');

        $expectedOcService->shouldReceive('getConfigFromData')
            ->with(['action' => 'A', 'foo' => 'bar'])
            ->andReturn('foobar')
            ->shouldReceive('getConfigFromData')
            ->with(['action' => 'A', 'foo' => 'bar1'])
            ->andReturn('foobar1')
            ->shouldReceive('getConfigFromData')
            ->with(['action' => 'U', 'foo' => 'cake'])
            ->andReturn('foocake')
            ->shouldReceive('getConfigFromData')
            ->with(['action' => 'D', 'foo' => 'blah'])
            ->andReturn('fooblah');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function psvProvider(): array
    {
        return [
            [
                true,
                'goodsOperatingCentreReviewService',
                'variationGoodsOcTotalAuthReviewService'
            ],
            [
                false,
                'psvOperatingCentreReviewService',
                'variationPsvOcTotalAuthReviewService'
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetHeaderTranslationKey')]
    public function testGetHeaderTranslationKey(mixed $vehicleTypeId, mixed $expectedTranslationKey): void
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

    public static function dpGetHeaderTranslationKey(): array
    {
        return [
            [RefData::APP_VEHICLE_TYPE_PSV, 'review-section-key'],
            [RefData::APP_VEHICLE_TYPE_HGV, 'review-section-key'],
            [RefData::APP_VEHICLE_TYPE_MIXED, 'review-section-key'],
            [RefData::APP_VEHICLE_TYPE_LGV, 'review-authorisation'],
        ];
    }
}
