<?php

/**
 * Fee number and status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\FeeNoAndStatus;
use Common\Service\Table\Formatter\FeeStatus;

/**
 * Fee number and status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeNoAndStatusTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals($expected, (new FeeNoAndStatus(new FeeStatus()))->format($data));
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
                    'id' => '99',
                    'feeStatus' => [
                        'id' => 'lfs_ot',
                        'description' => 'outstanding'
                    ],
                ],
                '99 <strong class="govuk-tag govuk-tag--orange">outstanding</strong>',
            ],
            'paid' => [
                [
                    'id' => '99',
                    'feeStatus' => [
                        'id' => 'lfs_pd',
                        'description' => 'paid'
                    ],
                ],
                '99 <strong class="govuk-tag govuk-tag--green">paid</strong>',
            ],
            'cancelled' => [
                [
                    'id' => '99',
                    'feeStatus' => [
                        'id' => 'lfs_cn',
                        'description' => 'cancelled'
                    ],
                ],
                '99 <strong class="govuk-tag govuk-tag--red">cancelled</strong>',
            ],
            'other' => [
                [
                    'id' => '99',
                    'feeStatus' => [
                        'id' => 'foo',
                        'description' => 'foo'
                    ],
                ],
                '99 <strong class="govuk-tag govuk-tag--grey">foo</strong>',
            ],
        ];
    }
}
