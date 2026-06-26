<?php

/**
 * Transaction Amount Sum formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\Money;
use Common\Service\Table\Formatter\TransactionAmountSum;

/**
 * Transaction Amount Sum formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionAmountSumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $column = ['name' => 'amount'];
        $this->assertSame($expected, (new TransactionAmountSum(new Money()))->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'no transactions' => [
                [],
                '£0.00'
            ],
            'invalid amounts' => [
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
            ],
            'one complete transaction' => [
                [
                    [
                        'status' => [
                            'id' => RefData::TRANSACTION_STATUS_COMPLETE
                        ],
                        'amount' => 5
                    ],
                ],
                '£5.00'
            ],
            'two complete transactions' => [
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
            ],
            'two complete one invalid' => [
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
            ],
            'one outstanding two complete' => [
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
            ],
        ];
    }
}
