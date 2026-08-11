<?php

/**
 * Transaction Amount Sum formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\Money;
use Common\Service\Table\Formatter\TransactionAmountSum;

/**
 * Transaction Amount Sum formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TransactionAmountSumTest extends \PHPUnit\Framework\TestCase
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
        $column = ['name' => 'amount'];
        $this->assertSame($expected, new TransactionAmountSum(new Money())->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'no transactions' => [
            [],
            '£0.00'
        ];
        yield 'invalid amounts' => [
            [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 'A'
                ],
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 'B'
                ],
            ],
            '£0.00'
        ];
        yield 'one complete transaction' => [
            [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 5
                ],
            ],
            '£5.00'
        ];
        yield 'two complete transactions' => [
            [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 5
                ],
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 7
                ],
            ],
            '£12.00'
        ];
        yield 'two complete one invalid' => [
            [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 5
                ],
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 7
                ],
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 'A'
                ]
            ],
            '£12.00'
        ];
        yield 'one outstanding two complete' => [
            [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_OUTSTANDING
                    ],
                    'amount' => 5
                ],
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 7
                ],
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE
                    ],
                    'amount' => 95
                ]
            ],
            '£102.00'
        ];
    }
}
