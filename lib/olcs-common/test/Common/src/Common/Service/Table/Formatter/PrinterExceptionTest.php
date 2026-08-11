<?php

/**
 * Printer exception formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\PrinterException;

/**
 * Printer exception formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrinterExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new PrinterException()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'team' => [
            [
                'team' => [
                    'name' => 'foo',
                ],
                'user' => null
            ],
            'foo',
        ];
        yield 'userWithName' => [
            [
                'user' => [
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'foo',
                            'familyName' => 'bar'
                        ]
                    ]
                ],
            ],
            'foo bar',
        ];
        yield 'userWithLoginId' => [
            [
                'user' => [
                    'loginId' => 'foo'
                ],
            ],
            'foo',
        ];
    }
}
