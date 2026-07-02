<?php

namespace CommonTest\Validator;

use Common\Validator\DateCompare;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Validator\DateCompare
 */
class ValidateDateCompareTest extends MockeryTestCase
{
    /**
     * test setOptions
     */
    public function testSetOptions(): void
    {
        $sut = new DateCompare();
        $sut->setOptions(
            [
                'compare_to' => 'test',
                'compare_to_label' => [null],
                'operator' => 'lt',
                'has_time' => false,
            ]
        );

        static::assertEquals('test', $sut->getCompareTo());
        static::assertEquals([null], $sut->getCompareToLabel());
        static::assertEquals('lt', $sut->getOperator());
        static::assertEquals(false, $sut->hasTime());
    }

    /**
     * @dataProvider provideIsValid
     */
    public function testIsValid($expected, $options, $value, $context, array $errorMessages = []): void
    {
        $errorMessages = ($errorMessages === [] ? ['error' => 'message'] : $errorMessages);

        $sut = new DateCompare();
        $sut->setOptions($options);
        static::assertEquals($expected, $sut->isValid($value, $context));

        if (!$expected) {
            static::assertEquals($errorMessages, $sut->getMessages());
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
                [
                    'other_field' => ['day' => '09', 'month' => '01', 'year' => '2014'],
                ],
            ],
            //context matches, field is invalid
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                [DateCompare::NOT_GT => "This date must be after 'Other field'"]
            ],
            //context doesn't match, field is invalid
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                [],
                [DateCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //missing context
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                [],
                [DateCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //Context field partially empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '', 'month' => '', 'year' => '2014']],
                [DateCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //context matches value is empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field'],
                '',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                [DateCompare::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //context matches, field is valid gte
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '10', 'month' => '01', 'year' => '2014']],
            ],
            //context matches, field is invalid gte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                [DateCompare::NOT_GTE => "This date must be after or the same as 'Other field'"]
            ],
            //context matches, field is valid lt
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
            ],
            //context matches, field is invalid lt
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompare::NOT_LT => "This date must be before 'Other field'"]
            ],
            //context matches, field is valid lte
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '10', 'month' => '01', 'year' => '2014']],
            ],
            //context matches, field has time and is valid gte
            [
                true,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'gte',
                    'compare_to_label' => 'Other field',
                    'has_time' => true,
                ],
                '2014-01-10 09:10:00',
                [
                    'other_field' => [
                        'day' => '10',
                        'month' => '01',
                        'year' => '2014',
                        'hour' => '09',
                        'minute' => '01',
                    ],
                ],
            ],
            //context matches, field has time and is valid lte
            [
                true,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'lte',
                    'compare_to_label' => 'Other field',
                    'has_time' => true,
                ],
                '2014-01-10 9:00:00',
                [
                    'other_field' => [
                        'day' => '10',
                        'month' => '01',
                        'year' => '2014',
                        'hour' => '09',
                        'minute' => '01',
                    ],
                ],
            ],
            //  field has time and is NOT valid lte
            [
                false,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'lt',
                    'compare_to_label' => 'Other field',
                    'has_time' => true,
                ],
                '2014-01-10 10:01:00',
                [
                    'other_field' => [
                        'year' => '2014',
                        'month' => '01',
                        'day' => '10',
                        'hour' => '10',
                        'minute' => '01',
                    ],
                ],
                [DateCompare::NOT_LT => "This date must be before 'Other field'"],
            ],
            //context matches, field is invalid lte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lte', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompare::NOT_LTE => "This date must be before or the same as 'Other field'"]
            ],
            //Invalid operator
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                '2014-01-10',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompare::INVALID_OPERATOR => 'Invalid operator']
            ],
            //Can't compare
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field'],
                '2014-01-',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompare::NO_COMPARE => "Unable to compare with 'Other field'"]
            ],
            //  compare DateTime against Date object
            [
                false,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'gt',
                    'compare_to_label' => 'Other field',
                    'has_time' => true,
                ],
                '2014-01-10 00:00:00',
                [
                    'other_field' => [
                        'year' => '2014',
                        'month' => '01',
                        'day' => '10',
                    ],
                ],
                [DateCompare::NOT_GT => "This date must be after 'Other field'"],
            ],

            //  compare Date against DateTime object
            [
                false,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'lte',
                    'compare_to_label' => 'Other field',
                    'has_time' => false,
                ],
                '2014-01-10',
                [
                    'other_field' => [
                        'year' => '2014',
                        'month' => '01',
                        'day' => '09',
                        'hour' => '23',
                        'minute' => '59',
                    ],
                ],
                [DateCompare::NOT_LTE => "This date must be before or the same as 'Other field'"],
            ],
        ];
    }
}
