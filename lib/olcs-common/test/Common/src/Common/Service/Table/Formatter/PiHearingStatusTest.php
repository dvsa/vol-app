<?php

/**
 * PI Hearing status formatter test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\PiHearingStatus;

/**
 * PI Hearing status formatter test
 */
final class PiHearingStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('PiHearingStatusFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new PiHearingStatus()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'cancelled' => [
            [
                'isCancelled' => 'Y',
                'isAdjourned' => 'N',
            ],
            '<span class="status red">CNL</span>',
        ];
        yield 'adjourned' => [
            [
                'isCancelled' => 'N',
                'isAdjourned' => 'Y',
            ],
            '<span class="status orange">ADJ</span>',
        ];
        yield 'other' => [
            [
                'isCancelled' => 'N',
                'isAdjourned' => 'N',
            ],
            '',
        ];
    }
}
