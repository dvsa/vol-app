<?php

/**
 * Irhp Permit Type Test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitType;

final class IrhpPermitTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitType()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'IRHP app' => [
            [
                'irhpPermitType' => [
                    'name' => [
                        'description' => 'IRHP type'
                    ],
                ],
            ],
            'IRHP type',
        ];
        yield 'ECMT app' => [
            [
                'permitType' => [
                    'description' => 'ECMT type'
                ],
            ],
            'ECMT type',
        ];
    }
}
