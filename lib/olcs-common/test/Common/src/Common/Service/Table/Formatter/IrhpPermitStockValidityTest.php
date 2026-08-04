<?php

/**
 * Irhp Permit Stock Validity formatter test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\IrhpPermitStockValidity;

final class IrhpPermitStockValidityTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new IrhpPermitStockValidity(new Date());
    }

    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('DateFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'Normal Dates' => [
            [
                'validFrom' => '2018-01-01T00:00:00+0000',
                'validTo' => '2019-12-31T23:59:59+0000',
            ],
            [
                'name' => 'validFrom',
            ],
            '01/01/2018 to 31/12/2019',
        ];
        yield 'Null From' => [
            [
                'validFrom' => null,
                'validTo' => '2019-12-31T23:59:59+0000',
            ],
            [
                'name' => 'validFrom',
            ],
            'N/A',
        ];
        yield 'Null To' => [
            [
                'validFrom' => '2018-01-01T00:00:00+0000',
                'validTo' => null,
            ],
            [
                'name' => 'validFrom',
            ],
            'N/A',
        ];
        yield 'Both Null' => [
            [
                'validFrom' => null,
                'validTo' => null,
            ],
            [
                'name' => 'validFrom',
            ],
            'N/A',
        ];
    }
}
