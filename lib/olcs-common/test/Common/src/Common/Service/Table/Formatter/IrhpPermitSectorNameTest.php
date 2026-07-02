<?php

/**
 * Irhp Permit Sector Name Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitSectorName;

class IrhpPermitSectorNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group IrhpPermitSectorFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitSectorName())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'empty description' => [
                [
                    'sector' => [
                        'name' => 'Mail and parcels',
                        'description' => '',
                    ],
                ],
                'Mail and parcels',
            ],
            'null description' => [
                [
                    'sector' => [
                        'name' => 'Mail and parcels',
                        'description' => null,
                    ],
                ],
                'Mail and parcels',
            ],
            'single description' => [
                [
                    'sector' => [
                        'name' => 'Food products',
                        'description' => 'B',
                    ],
                ],
                'Food products: B',
            ],
            'has description' => [
                [
                    'sector' => [
                        'name' => 'Food products',
                        'description' => 'Beverages and tobacco, products of agriculture',
                    ],
                ],
                'Food products: Beverages and tobacco, products of agriculture',
            ],
        ];
    }
}
