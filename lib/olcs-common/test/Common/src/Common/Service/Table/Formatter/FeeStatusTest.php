<?php

/**
 * Fee status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

/**
 * Fee status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class FeeStatusTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals($expected, new \Common\Service\Table\Formatter\FeeStatus()->format($data));
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
                'feeStatus' => [
                    'id' => 'lfs_ot',
                    'description' => 'outstanding'
                ],
            ],
            '<strong class="govuk-tag govuk-tag--orange">outstanding</strong>',
        ];
        yield 'paid' => [
            [
                'feeStatus' => [
                    'id' => 'lfs_pd',
                    'description' => 'paid'
                ],
            ],
            '<strong class="govuk-tag govuk-tag--green">paid</strong>',
        ];
        yield 'cancelled' => [
            [
                'feeStatus' => [
                    'id' => 'lfs_cn',
                    'description' => 'cancelled'
                ],
            ],
            '<strong class="govuk-tag govuk-tag--red">cancelled</strong>',
        ];
        yield 'other' => [
            [
                'feeStatus' => [
                    'id' => 'foo',
                    'description' => 'foo'
                ],
            ],
            '<strong class="govuk-tag govuk-tag--grey">foo</strong>',
        ];
    }
}
