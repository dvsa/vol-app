<?php

/**
 * Hide If Closed Radio Formatter Test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Hide If Closed Radio Formatter Test
 */
final class HideIfClosedRadioTest extends MockeryTestCase
{
    /**
     * Test formatter
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new \Common\Service\Table\Formatter\HideIfClosedRadio()->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(int | string)> | string)>>
     *
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'closedDate' => '2015-03-24',
                'id' => 1
            ],
            ''
        ];
        yield [
            [
                'closedDate' => '',
                'id' => 1
            ],
            '<input type="radio" id="submission-1" value="1" name="id"><label class="govuk-visually-hidden" for="submission-1">Select submission 1</label>'
        ];
        yield [
            [
                'id' => 1
            ],
            '<input type="radio" id="submission-1" value="1" name="id"><label class="govuk-visually-hidden" for="submission-1">Select submission 1</label>'
        ];
    }
}
