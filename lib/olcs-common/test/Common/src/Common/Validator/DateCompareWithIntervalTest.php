<?php

namespace CommonTest\Validator;

use Common\Validator\DateCompareWithInterval;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Date Compare With Interval Validator Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class DateCompareWithIntervalTest extends MockeryTestCase
{
    /**
     * test setOptions
     */
    public function testSetOptions(): void
    {
        $sut = new DateCompareWithInterval();
        $sut->setOptions(
            [
                'compare_to' => 'test',
                'compare_to_label' => [null],
                'interval_label' => 'X days',
                'date_interval' => 'P5D',
                'operator' => 'lt',
                'has_time' => false
            ]
        );

        static::assertEquals('test', $sut->getCompareTo());
        static::assertEquals([null], $sut->getCompareToLabel());
        static::assertEquals('lt', $sut->getOperator());
        static::assertEquals(false, $sut->hasTime());
        static::assertEquals('P5D', $sut->getDateInterval());
        static::assertEquals('X days', $sut->getIntervalLabel());
    }

    /**
     * @dataProvider provideIsValid
     */
    public function testIsValid($expected, $options, $value, $context, $errorMessages = []): void
    {
        $errorMessages = empty($errorMessages) ? ['error' => 'message'] : $errorMessages;

        $sut = new DateCompareWithInterval();
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
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-11',
                ['other_field' =>
                    ['day' => '09', 'month' => '01', 'year' => '2014'], true],
                true
            ],
            //context matches, field is invalid
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-10',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::NOT_GT => "This date must be 2 days after the 'Other field'"]
            ],
            //context doesn't match, field is invalid
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-10',
                [],
                [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //missing context
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-10',
                [],
                [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //Context field partially empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-10',
                ['other_field' => ['day' => '', 'month' => '', 'year' => '2014']],
                [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //context matches value is empty
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
            ],
            //context matches, field is valid, invalid operator gte
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-10',
                ['other_field' => ['day' => '10', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::INVALID_OPERATOR => 'Invalid operator']
            ],
            //context matches, field has time and is valid, valid gt
            [
                true,
                [
                    'compare_to' => 'other_field',
                    'operator' => 'gt',
                    'compare_to_label' => 'Other field',
                    'has_time' => true,
                    'date_interval' => 'P2D',
                    'interval_label' => '2 days',
                ],
                '2014-01-12 10:00:00',
                [
                    'other_field' => [
                        'day' => '10',
                        'month' => '01',
                        'year' => '2014',
                        'hour' => '09',
                        'minute' => '10',
                    ],
                ],
                true,
            ],
            //context matches, field is invalid gt
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-12',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::NOT_GT => "This date must be 2 days after the 'Other field'"]
            ],
            //context matches, field is valid lt
            [
                true,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-09',
                ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
                true
            ],
            //context matches, field is invalid lt
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-08',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::NOT_LT => "This date must be 2 days before 'Other field'"]
            ],
            // Invalid Interval
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field',
                    'date_interval' => 'INVALID', 'interval_label' => '2 days'],
                '2014-01-08',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::INVALID_INTERVAL => 'Invalid interval']
            ],
            //Invalid operator
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-10',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::INVALID_OPERATOR => 'Invalid operator']
            ],
            //Can't compare
            [
                false,
                ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field',
                    'date_interval' => 'P2D', 'interval_label' => '2 days'],
                '2014-01-',
                ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
                [DateCompareWithInterval::NO_COMPARE => "Unable to compare with 'Other field'"]
            ]
        ];
    }
}
