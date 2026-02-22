<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TieredProductReferenceTraitTest
 */
class TieredProductReferenceTest extends MockeryTestCase
{
    public const PROD_REFS = [
        'Jan' => FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
        'Feb' => FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
        'Mar' => FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
        'Apr' => FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
        'May' => FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
        'Jun' => FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
        'Jul' => FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
        'Aug' => FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
        'Sep' => FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
        'Oct' => FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
        'Nov' => FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
        'Dec' => FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
    ];

    public function testGenericGetProdRefForTierFuture(): void
    {
        $sut = new StubTieredProductReference();
        $this->assertEquals(
            FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
            $sut->genericGetProdRefForTier(
                new DateTime('9999-01-01'),
                new DateTime('9999-12-31'),
                new DateTime(),
                self::PROD_REFS
            )
        );
    }

    public function testGenericGetProdRefForTierPast(): void
    {
        $sut = new StubTieredProductReference();
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(
            'Cannot get issue fee type for permit with window validity period in the past.'
        );

        $sut->genericGetProdRefForTier(
            new DateTime('1999-01-01'),
            new DateTime('1999-12-31'),
            new DateTime(),
            self::PROD_REFS
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('productRefMonthProvider')]
    public function testGenericGetProdRefForTierMonths(mixed $expected, mixed $validFrom, mixed $validTo, mixed $now, mixed $refs): void
    {
        $sut = new StubTieredProductReference();
        $this->assertEquals(
            $expected,
            $sut->genericGetProdRefForTier(
                $validFrom,
                $validTo,
                $now,
                $refs
            )
        );
    }

    public static function productRefMonthProvider(): array
    {
        $validFrom = new DateTime('first day of January next year');
        $validTo = new DateTime('last day of December next year');

        return [
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of January next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of February next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of March next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of April next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of May next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of June next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of July next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of August next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of September next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of October next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of November next year'),
                self::PROD_REFS
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of December next year'),
                self::PROD_REFS
            ],
        ];
    }
}
