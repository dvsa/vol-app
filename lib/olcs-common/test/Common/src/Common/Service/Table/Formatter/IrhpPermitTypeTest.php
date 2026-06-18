<?php

/**
 * Irhp Permit Type Test
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitType;

class IrhpPermitTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitType())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'IRHP app' => [
                [
                    'irhpPermitType' => [
                        'name' => [
                            'description' => 'IRHP type'
                        ],
                    ],
                ],
                'IRHP type',
            ],
            'ECMT app' => [
                [
                    'permitType' => [
                        'description' => 'ECMT type'
                    ],
                ],
                'ECMT type',
            ],
        ];
    }
}
