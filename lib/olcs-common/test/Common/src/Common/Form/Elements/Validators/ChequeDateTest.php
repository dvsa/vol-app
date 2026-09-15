<?php

/**
 * Cheque Date Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\ChequeDate;

/**
 * Cheque Date Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ChequeDateTest extends \PHPUnit\Framework\TestCase
{
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new ChequeDate();
    }

    #[\PHPUnit\Framework\Attributes\Group('validators')]
    #[\PHPUnit\Framework\Attributes\Group('date_validators')]
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($input));
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return list{list{string, true}, list{string, true}, list{string, true}, list{string, false}}
     */
    public static function providerIsValid(): \Iterator
    {
        yield [
            date('Y-m-d'),
            true
        ];
        yield [
            date('Y-m-d', strtotime('-1 month')),
            true
        ];
        yield [
            date('Y-m-d', strtotime('-6 months')),
            true
        ];
        yield [
            date('Y-m-d', strtotime('-7 months')),
            false
        ];
    }
}
