<?php

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\SumCompare;

/**
 * Class NumberCompare
 * @package CommonTest\Validator
 */
class SumCompareTest extends MockeryTestCase
{
    /**
     * test setOptions
     */
    public function testSetOptions(): void
    {
        $sut = new SumCompare();
        $sut->setOptions(
            [
                'compare_to' => 'test',
                'sum_with' => 'sum_with',
                'compare_to_label' => [null],
                'operator' => 'lt',
                'allow_empty' => true
            ]
        );

        $this->assertEquals('test', $sut->getCompareTo());
        $this->assertEquals('sum_with', $sut->getSumWith());
        $this->assertEquals(true, $sut->getAllowEmpty());
        $this->assertEquals([null], $sut->getCompareToLabel());
        $this->assertEquals('lt', $sut->getOperator());
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
        $sut = new SumCompare();
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
                ['allow_empty' => false, 'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                101,
                [],
                [SumCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            // context matches value is empty
            [
                false,
                ['allow_empty' => false,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '',
                ['other_field' => 100, 'sum_with' => 10],
                [SumCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            // context allowed empty
            [
                true,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '',
                ['other_field' => 100, 'sum_with' => 10],
                [SumCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            // field is valid gt
            [
                true,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                101,
                ['other_field' => 100, 'sum_with' => 10],
            ],
            // field is invalid gt
            [
                false,
                ['allow_empty' => true, 'sum_with' => 'sum_with',  'compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                90,
                ['other_field' => 100, 'sum_with' => 9],
                [SumCompare::NOT_GT => "The sum must be greater than 'Other field'"],
            ],
            // field is valid gte
            [
                true,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                98,
                ['other_field' => 99, 'sum_with' => 1],
            ],
            // field is invalid gte
            [
                false,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                98,
                ['other_field' => 100, 'sum_with' => 1],
                [SumCompare::NOT_GTE => "The sum must be greater than or equal to 'Other field'"],
            ],
            // field is valid lt
            [
                true,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                96,
                ['other_field' => 98, 'sum_with' => 1],
            ],
            // field is invalid lt
            [
                false,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                96,
                ['other_field' => 100, 'sum_with' => 5],
                [SumCompare::NOT_LT => "The sum must be less than 'Other field'"],
            ],
            // field is valid lte
            [
                true,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                90,
                ['other_field' => 100, 'sum_with' => 10],
            ],
            // field is invalid lte
            [
                false,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                90,
                ['other_field' => 100, 'sum_with' => 11],
                [SumCompare::NOT_LTE => "The sum must be less than or equal to 'Other field'"],
            ],
            // invalid operator
            [
                false,
                ['allow_empty' => true,  'sum_with' => 'sum_with', 'compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                100,
                ['other_field' => 100, 'sum_with' => 10],
                [SumCompare::INVALID_OPERATOR => 'Invalid operator']
            ],
        ];
    }
}
