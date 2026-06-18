<?php

/**
 * Irhp Permit Stock Country formatter test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitStockCountry;

class IrhpPermitStockCountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider dpFormat
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals(
            $expected,
            (new IrhpPermitStockCountry())->format($data)
        );
    }

    /**
     * @return (string|string[][])[][]
     *
     * @psalm-return array{'No country': list{array<never, never>, 'N/A'}, 'Country only': list{array{country: array{countryDesc: 'Bosnia & Herzegovina'}}, 'Bosnia &amp; Herzegovina'}, 'Country and permit category': list{array{country: array{countryDesc: 'Bosnia & Herzegovina'}, permitCategory: array{description: 'Hors contingent'}}, 'Bosnia &amp; Herzegovina Hors contingent'}}
     */
    public function dpFormat(): array
    {
        return [
            'No country' => [
                [],
                'N/A',
            ],
            'Country only' => [
                [
                    'country' => [
                        'countryDesc' => 'Bosnia & Herzegovina'
                    ]
                ],
                'Bosnia &amp; Herzegovina',
            ],
            'Country and permit category' => [
                [
                    'country' => [
                        'countryDesc' => 'Bosnia & Herzegovina'
                    ],
                    'permitCategory' => [
                        'description' => 'Hors contingent'
                    ]
                ],
                'Bosnia &amp; Herzegovina Hors contingent',
            ],
        ];
    }
}
