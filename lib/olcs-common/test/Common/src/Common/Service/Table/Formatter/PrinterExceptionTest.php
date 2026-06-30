<?php

/**
 * Printer exception formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\PrinterException;

/**
 * Printer exception formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new PrinterException())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'team' => [
                [
                    'team' => [
                        'name' => 'foo',
                    ],
                    'user' => null
                ],
                'foo',
            ],
            'userWithName' => [
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
            ],
            'userWithLoginId' => [
                [
                    'user' => [
                        'loginId' => 'foo'
                    ],
                ],
                'foo',
            ]
        ];
    }
}
