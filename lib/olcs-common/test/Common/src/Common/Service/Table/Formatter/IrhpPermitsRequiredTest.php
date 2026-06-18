<?php

/**
 * Irhp Permits Required Test
 */

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\IrhpPermitsRequired;

class IrhpPermitsRequiredTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider dpFormatProvider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitsRequired())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function dpFormatProvider()
    {
        return [
            [
                [
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                123,
            ],
            [
                [
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                123,
            ],
            [
                [
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                123,
            ],
            [
                [
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                123,
            ],
            [
                [
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                123,
            ],
            [
                [
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                1,
            ],
            [
                [
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'permitsRequired' => 123
                ],
                1,
            ],
        ];
    }
}
