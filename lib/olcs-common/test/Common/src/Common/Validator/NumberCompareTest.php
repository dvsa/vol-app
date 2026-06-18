<?php

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\NumberCompare;

/**
 * Class NumberCompare
 * @package CommonTest\Validator
 */
class NumberCompareTest extends MockeryTestCase
{
    /**
     * test setOptions
     */
    public function testSetOptions(): void
    {
        $sut = new NumberCompare();
        $sut->setOptions(
            [
                'compare_to' => 'test',
                'compare_to_label' => [null],
                'operator' => 'lt',
                'max_diff' => 100
            ]
        );

        $this->assertEquals('test', $sut->getCompareTo());
        $this->assertEquals([null], $sut->getCompareToLabel());
        $this->assertEquals('lt', $sut->getOperator());
        $this->assertEquals(100, $sut->getMaxDiff());
    }

    /**
     * @dataProvider provideIsValid
     * @param $expected
     * @param $options
     * @param $value
     * @param $context
     * @param array $errorMessages
     */
    public function testIsValid($expected, $options, $value, $context, $errorMessages = []): void
    {
        $sut = new NumberCompare();
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->isValid($value, $context));

        if (!$expected) {
            $this->assertEquals($errorMessages, $sut->getMessages());
        }
    }

    /**
     * @return array
     */
    public function provideIsValid()
    {
        return [
            // missing context
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                101,
                [],
                [NumberCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            // context matches value is empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '',
                ['other_field' => 100],
                [NumberCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            // field is valid gt
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                101,
                ['other_field' => 100],
            ],
            // field is invalid gt
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                100,
                ['other_field' => 100],
                [NumberCompare::NOT_GT => "This number must be greater than 'Other field'"],
            ],
            // field is valid gte
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                100,
                ['other_field' => 100],
            ],
            // field is invalid gte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                99,
                ['other_field' => 100],
                [NumberCompare::NOT_GTE => "This number must be greater than or equal to 'Other field'"],
            ],
            // field is valid lt
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                99,
                ['other_field' => 100],
            ],
            // field is invalid lt
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                100,
                ['other_field' => 100],
                [NumberCompare::NOT_LT => "This number must be less than 'Other field'"],
            ],
            // field is valid lte
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                100,
                ['other_field' => 100],
            ],
            // field is invalid lte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                101,
                ['other_field' => 100],
                [NumberCompare::NOT_LTE => "This number must be less than or equal to 'Other field'"],
            ],
            // field is valid gte within max diff
            [
                true,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'gte',
                    'compare_to_label' => 'Other field',
                    'max_diff' => 50
                ],
                150,
                ['other_field' => 100],
            ],
            // field is valid gte but exceeded max diff
            [
                false,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'gte',
                    'compare_to_label' => 'Other field',
                    'max_diff' => 50
                ],
                151,
                ['other_field' => 100],
                [
                    NumberCompare::MAX_DIFF_EXCEEDED
                        => "Difference between this number and 'Other field' must be less than or equal to 50"
                ],
            ],
            // invalid operator
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                100,
                ['other_field' => 100],
                [NumberCompare::INVALID_OPERATOR => 'Invalid operator']
            ],
        ];
    }
}
