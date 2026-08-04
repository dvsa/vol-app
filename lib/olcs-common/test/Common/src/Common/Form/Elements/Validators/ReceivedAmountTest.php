<?php

/**
 * Received Amount Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\ReceivedAmount as Sut;

/**
 * Received Amount Validator Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ReceivedAmountTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Sut();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $context));
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool | string | null)>>
     *
     * @psalm-return list{list{'0', null, true}, list{'100', null, true}, list{'100', array{minAmountForValidator: '10'}, true}, list{'10', array{minAmountForValidator: '10'}, true}, list{'9.99', array{minAmountForValidator: '10'}, false}}
     */
    public static function isValidProvider(): \Iterator
    {
        yield [
            '0',
            null,
            true,
        ];
        yield [
            '100',
            null,
            true,
        ];
        yield [
            '100',
            [
                'minAmountForValidator' => '10',
            ],
            true,
        ];
        yield [
            '10',
            [
                'minAmountForValidator' => '10',
            ],
            true,
        ];
        yield [
            '9.99',
            [
                'minAmountForValidator' => '10',
            ],
            false,
        ];
    }
}
