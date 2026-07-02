<?php

/**
 * Cheque Date Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\ChequeDate;

/**
 * Cheque Date Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ChequeDateTest extends \PHPUnit\Framework\TestCase
{
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new ChequeDate();
    }

    /**
     * @group validators
     * @group date_validators
     * @dataProvider providerIsValid
     */
    public function testIsValid($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($input));
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{string, true}, list{string, true}, list{string, true}, list{string, false}}
     */
    public function providerIsValid(): array
    {
        return [
            [
                date('Y-m-d'),
                true
            ],
            [
                date('Y-m-d', strtotime('-1 month')),
                true
            ],
            [
                date('Y-m-d', strtotime('-6 months')),
                true
            ],
            [
                date('Y-m-d', strtotime('-7 months')),
                false
            ]
        ];
    }
}
