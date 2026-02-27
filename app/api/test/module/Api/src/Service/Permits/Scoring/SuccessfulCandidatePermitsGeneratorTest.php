<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\EmissionsCategoryAvailabilityCounter;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SuccessfulCandidatePermitsGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SuccessfulCandidatePermitsGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerate')]
    public function testGenerate(mixed $euro5PermitsRemaining, mixed $euro6PermitsRemaining, array $expectedSuccessful): void
    {
        $stockId = 72;
        $quotaRemaining = 13;

        $candidatePermits = [
            ['id' => 1, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 2, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 3, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 4, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 5, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 6, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 7, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 8, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 9, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 10, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 11, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 12, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 13, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 14, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 15, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 16, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 17, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 18, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 19, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 20, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 21, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 22, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 23, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 24, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 25, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 26, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 27, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];

        $emissionsCategoryAvailabilityCounter = m::mock(EmissionsCategoryAvailabilityCounter::class);
        $emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5PermitsRemaining);
        $emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6PermitsRemaining);

        $successfulCandidatePermitsGenerator = new SuccessfulCandidatePermitsGenerator(
            $emissionsCategoryAvailabilityCounter
        );

        $this->assertEquals(
            $expectedSuccessful,
            $successfulCandidatePermitsGenerator->generate($candidatePermits, $stockId, $quotaRemaining)
        );
    }

    public static function dpTestGenerate(): array
    {
        return [
            'more euro 6 than euro 5 remaining' => [
                5,
                10,
                [
                    ['id' => 1, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 2, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 3, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 4, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 5, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 6, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 7, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 8, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 9, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 10, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 12, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 14, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 18, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                ]
            ],
            'more euro 5 than euro 6 remaining' => [
                10,
                5,
                [
                    ['id' => 1, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 2, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 3, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 4, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 5, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 6, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 7, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 8, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 9, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 10, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
                    ['id' => 11, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 12, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                    ['id' => 13, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
                ]
            ]
        ];
    }
}
