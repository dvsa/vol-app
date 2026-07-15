<?php

/**
 * Transaction status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\TransactionStatus as Sut;

/**
 * Transaction status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TransactionStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('FeeStatusFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new Sut()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'outstanding' => [
            [
                'status' => [
                    'id' => RefData::TRANSACTION_STATUS_OUTSTANDING,
                    'description' => 'outstanding',
                ],
            ],
            '<strong class="govuk-tag govuk-tag--orange">outstanding</strong>',
        ];
        yield 'complete' => [
            [
                'status' => [
                    'id' => RefData::TRANSACTION_STATUS_COMPLETE,
                    'description' => 'complete',
                ],
            ],
            '<strong class="govuk-tag govuk-tag--green">complete</strong>',
        ];
        yield 'cancelled' => [
            [
                'status' => [
                    'id' => RefData::TRANSACTION_STATUS_CANCELLED,
                    'description' => 'cancelled',
                ],
            ],
            '<strong class="govuk-tag govuk-tag--red">cancelled</strong>',
        ];
        yield 'failed' => [
            [
                'status' => [
                    'id' => RefData::TRANSACTION_STATUS_FAILED,
                    'description' => 'failed',
                ],
            ],
            '<strong class="govuk-tag govuk-tag--red">failed</strong>',
        ];
        yield 'other' => [
            [
                'status' => [
                    'id' => 'foo',
                    'description' => 'bar',
                ],
            ],
            '<strong class="govuk-tag govuk-tag--grey">bar</strong>',
        ];
        yield 'migrated' => [
            [
                'status' => [
                    'id' => RefData::TRANSACTION_STATUS_FAILED,
                    'description' => 'failed',
                ],
                'migratedFromOlbs' => true,
            ],
            '<strong class="govuk-tag govuk-tag--red">Migrated</strong>',
        ];
    }
}
