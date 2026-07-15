<?php

/**
 * Irhp Permit Sector Name Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitSectorName;

final class IrhpPermitSectorNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('IrhpPermitSectorFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitSectorName()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'empty description' => [
            [
                'sector' => [
                    'name' => 'Mail and parcels',
                    'description' => '',
                ],
            ],
            'Mail and parcels',
        ];
        yield 'null description' => [
            [
                'sector' => [
                    'name' => 'Mail and parcels',
                    'description' => null,
                ],
            ],
            'Mail and parcels',
        ];
        yield 'single description' => [
            [
                'sector' => [
                    'name' => 'Food products',
                    'description' => 'B',
                ],
            ],
            'Food products: B',
        ];
        yield 'has description' => [
            [
                'sector' => [
                    'name' => 'Food products',
                    'description' => 'Beverages and tobacco, products of agriculture',
                ],
            ],
            'Food products: Beverages and tobacco, products of agriculture',
        ];
    }
}
