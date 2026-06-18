<?php

/**
 * Test Withdrawn date validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\WithdrawnDate;

/**
 * Test Withdrawn date validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class WithdrawnDateValidatorTest extends \PHPUnit\Framework\TestCase
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
     *
     * @dataProvider providerIsValid
     */
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, $context));
    }

    /**
     * Provider for isValid
     *
     * @return array
     */
    public function providerIsValid()
    {
        return [
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ]
        ];
    }
}
