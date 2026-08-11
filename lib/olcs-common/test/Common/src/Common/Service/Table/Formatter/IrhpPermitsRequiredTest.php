<?php

/**
 * Irhp Permits Required Test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\IrhpPermitsRequired;

final class IrhpPermitsRequiredTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\DataProvider('dpFormatProvider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitsRequired()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function dpFormatProvider(): \Iterator
    {
        yield [
            [
                'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            123,
        ];
        yield [
            [
                'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            123,
        ];
        yield [
            [
                'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            123,
        ];
        yield [
            [
                'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            123,
        ];
        yield [
            [
                'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            123,
        ];
        yield [
            [
                'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            1,
        ];
        yield [
            [
                'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                'permitsRequired' => 123
            ],
            1,
        ];
    }
}
