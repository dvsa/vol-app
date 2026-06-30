<?php

/**
 * Received Amount Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\ReceivedAmount as Sut;

/**
 * Received Amount Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReceivedAmountTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Sut();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $context));
    }

    /**
     * @return (bool|null|string|string[])[][]
     *
     * @psalm-return list{list{'0', null, true}, list{'100', null, true}, list{'100', array{minAmountForValidator: '10'}, true}, list{'10', array{minAmountForValidator: '10'}, true}, list{'9.99', array{minAmountForValidator: '10'}, false}}
     */
    public function isValidProvider(): array
    {
        return [
            [
                '0',
                null,
                true,
            ],
            [
                '100',
                null,
                true,
            ],
            [
                '100',
                [
                    'minAmountForValidator' => '10',
                ],
                true,
            ],
            [
                '10',
                [
                    'minAmountForValidator' => '10',
                ],
                true,
            ],
            [
                '9.99',
                [
                    'minAmountForValidator' => '10',
                ],
                false,
            ],
        ];
    }
}
