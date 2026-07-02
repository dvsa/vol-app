<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Validators\DateCompare;

/**
 * Class DateCompareTest
 */
class DateCompareTest extends MockeryTestCase
{
    /**
     * test setOptions
     */
    public function testSetOptions()
    {
        $sut = new DateCompare();
        $sut->setOptions(
            [
                'compare_to' => 'test',
                'compare_to_label' => [null],
                'operator' => 'lt',
                'has_time' => false
            ]
        );

        $this->assertEquals('test', $sut->getCompareTo());
        $this->assertEquals([null], $sut->getCompareToLabel());
        $this->assertEquals('lt', $sut->getOperator());
        $this->assertEquals(false, $sut->getHasTime());
    }

    /**
     * @dataProvider provideIsValid
     * @param $expected
     * @param $options
     * @param $value
     * @param $context
     * @param array $errorMessages
     */
    public function testIsValid($expected, $options, $value, $context, $errorMessages = [])
    {
        $errorMessages = empty($errorMessages) ? ['error' => 'message'] : $errorMessages;

        $sut = new DateCompare();
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
            //context matches, field is valid
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-09'],
                true
            ],
            //context matches, field is invalid
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-11'],
                [DateCompare::NOT_GT => 'This date must be after \'Other field\'']
            ],
            //context doesn't match, field is invalid
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                [],
                [DateCompare::INVALID_FIELD => 'Input field being compared to doesn\'t exist']
            ],
            //missing context
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                [],
                [DateCompare::INVALID_FIELD => 'Input field being compared to doesn\'t exist']
            ],
            //Context field partially empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ''],
                [DateCompare::INVALID_FIELD => 'Input field being compared to doesn\'t exist']
            ],
            //context matches value is empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '',
                ['other_field' => '2014-01-11'],
                [DateCompare::INVALID_FIELD => 'Input field being compared to doesn\'t exist']
            ],
            //context matches, field is valid gte
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-10'],
                true
            ],
            //context matches, field has time and is valid gte
            [
                true,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'gte',
                    'compare_to_label' => 'Other field',
                    'has_time' => true
                ],
                '2014-01-10 10:00:00',
                ['other_field' => '2014-01-10'],
                true
            ],
            //context matches, field is invalid gte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-11'],
                [DateCompare::NOT_GTE => 'This date must be after or the same as \'Other field\'']
            ],
            //context matches, field is valid lt
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-11'],
                true
            ],
            //context matches, field is invalid lt
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-09'],
                [DateCompare::NOT_LT => 'This date must be before \'Other field\'']
            ],
            //context matches, field is valid lte
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-10'],
                true
            ],
            //context matches, field has time and is valid lte
            [
                true,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'lte',
                    'compare_to_label' => 'Other field',
                    'has_time' => true
                ],
                '2014-01-10 10:00:00',
                ['other_field' => '2014-01-10'],
                true
            ],
            //context matches, field is invalid lte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-09'],
                [DateCompare::NOT_LTE => 'This date must be before or the same as \'Other field\'']
            ],
            //Invalid operator
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => '2014-01-09'],
                [DateCompare::INVALID_OPERATOR => 'Invalid operator']
            ],
            //Can't compare
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                '2014-01-',
                ['other_field' => '2014-01-09'],
                [DateCompare::NO_COMPARE => "Unable to compare with 'Other field'"]
            ],
        ];
    }
}
