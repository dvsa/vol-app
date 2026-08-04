<?php

/**
 * Irhp Permit Stock Country formatter test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitStockCountry;

final class IrhpPermitStockCountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\DataProvider('dpFormat')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals(
            $expected,
            new IrhpPermitStockCountry()->format($data)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<string>> | string)>>
     *
     * @psalm-return array{'No country': list{array<never, never>, 'N/A'}, 'Country only': list{array{country: array{countryDesc: 'Bosnia & Herzegovina'}}, 'Bosnia &amp; Herzegovina'}, 'Country and permit category': list{array{country: array{countryDesc: 'Bosnia & Herzegovina'}, permitCategory: array{description: 'Hors contingent'}}, 'Bosnia &amp; Herzegovina Hors contingent'}}
     */
    public static function dpFormat(): \Iterator
    {
        yield 'No country' => [
            [],
            'N/A',
        ];
        yield 'Country only' => [
            [
                'country' => [
                    'countryDesc' => 'Bosnia & Herzegovina'
                ]
            ],
            'Bosnia &amp; Herzegovina',
        ];
        yield 'Country and permit category' => [
            [
                'country' => [
                    'countryDesc' => 'Bosnia & Herzegovina'
                ],
                'permitCategory' => [
                    'description' => 'Hors contingent'
                ]
            ],
            'Bosnia &amp; Herzegovina Hors contingent',
        ];
    }
}
