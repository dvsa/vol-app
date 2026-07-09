<?php

/**
 * Test Withdrawn date validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\WithdrawnDate;

/**
 * Test Withdrawn date validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class WithdrawnDateValidatorTest extends \PHPUnit\Framework\TestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new WithdrawnDate();
    }

    /**
     * Test isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, $context));
    }

    /**
     * Provider for isValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function providerIsValid(): \Iterator
    {
        yield [
            '2014-01-01',
            [
                'isWithdrawn' => 'Y',
                'withdrawnDate' => [
                    'year' => '2014',
                    'month' => '01',
                    'day' => '01'
                ]
            ],
            true
        ];
        yield [
            '2014-01-32',
            [
                'isWithdrawn' => 'Y',
                'withdrawnDate' => [
                    'year' => '2014',
                    'month' => '01',
                    'day' => '32'
                ]
            ],
            false
        ];
        yield [
            '2014-02-30',
            [
                'isWithdrawn' => 'Y',
                'withdrawnDate' => [
                    'year' => '2014',
                    'month' => '02',
                    'day' => '30'
                ]
            ],
            false
        ];
        yield [
            '2100-12-31',
            [
                'isWithdrawn' => 'Y',
                'withdrawnDate' => [
                    'year' => '2100',
                    'month' => '12',
                    'day' => '31'
                ]
            ],
            false
        ];
        yield [
            null,
            [
                'isWithdrawn' => 'N',
                'withdrawnDate' => [
                    'year' => '',
                    'month' => '',
                    'day' => ''
                ]
            ],
            true
        ];
    }
}
