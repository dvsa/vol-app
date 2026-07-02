<?php

/**
 * Fee status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

/**
 * Fee status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeStatusTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\FeeStatus())->format($data));
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
                    'feeStatus' => [
                        'id' => 'lfs_ot',
                        'description' => 'outstanding'
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--orange">outstanding</strong>',
            ],
            'paid' => [
                [
                    'feeStatus' => [
                        'id' => 'lfs_pd',
                        'description' => 'paid'
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--green">paid</strong>',
            ],
            'cancelled' => [
                [
                    'feeStatus' => [
                        'id' => 'lfs_cn',
                        'description' => 'cancelled'
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--red">cancelled</strong>',
            ],
            'other' => [
                [
                    'feeStatus' => [
                        'id' => 'foo',
                        'description' => 'foo'
                    ],
                ],
                '<strong class="govuk-tag govuk-tag--grey">foo</strong>',
            ],
        ];
    }
}
