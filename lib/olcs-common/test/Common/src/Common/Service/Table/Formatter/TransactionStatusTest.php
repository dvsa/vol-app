<?php

/**
 * Transaction status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\TransactionStatus as Sut;

/**
 * Transaction status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group FeeStatusFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new Sut())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'outstanding' => [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_OUTSTANDING,
                        'description' => 'outstanding',
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--orange">outstanding</strong>',
            ],
            'complete' => [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_COMPLETE,
                        'description' => 'complete',
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--green">complete</strong>',
            ],
            'cancelled' => [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_CANCELLED,
                        'description' => 'cancelled',
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--red">cancelled</strong>',
            ],
            'failed' => [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_FAILED,
                        'description' => 'failed',
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--red">failed</strong>',
            ],
            'other' => [
                [
                    'status' => [
                        'id' => 'foo',
                        'description' => 'bar',
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--grey">bar</strong>',
            ],
            'migrated' => [
                [
                    'status' => [
                        'id' => RefData::TRANSACTION_STATUS_FAILED,
                        'description' => 'failed',
                    ],
                    'migratedFromOlbs' => true,
                ],
                '<strong class="govuk-tag govuk-tag--red">Migrated</strong>',
            ],
        ];
    }
}
