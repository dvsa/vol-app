<?php

/**
 * Fee number and status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\FeeNoAndStatus;
use Common\Service\Table\Formatter\FeeStatus;

/**
 * Fee number and status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class FeeNoAndStatusTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals($expected, new FeeNoAndStatus(new FeeStatus())->format($data));
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
                'id' => '99',
                'feeStatus' => [
                    'id' => 'lfs_ot',
                    'description' => 'outstanding'
                ],
            ],
            '99 <strong class="govuk-tag govuk-tag--orange">outstanding</strong>',
        ];
        yield 'paid' => [
            [
                'id' => '99',
                'feeStatus' => [
                    'id' => 'lfs_pd',
                    'description' => 'paid'
                ],
            ],
            '99 <strong class="govuk-tag govuk-tag--green">paid</strong>',
        ];
        yield 'cancelled' => [
            [
                'id' => '99',
                'feeStatus' => [
                    'id' => 'lfs_cn',
                    'description' => 'cancelled'
                ],
            ],
            '99 <strong class="govuk-tag govuk-tag--red">cancelled</strong>',
        ];
        yield 'other' => [
            [
                'id' => '99',
                'feeStatus' => [
                    'id' => 'foo',
                    'description' => 'foo'
                ],
            ],
            '99 <strong class="govuk-tag govuk-tag--grey">foo</strong>',
        ];
    }
}
