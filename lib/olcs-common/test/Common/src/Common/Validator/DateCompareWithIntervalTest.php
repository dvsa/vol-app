<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Common\Validator\DateCompareWithInterval;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Date Compare With Interval Validator Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class DateCompareWithIntervalTest extends MockeryTestCase
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

        $this->assertEquals('test', $sut->getCompareTo());
        $this->assertEquals([null], $sut->getCompareToLabel());
        $this->assertEquals('lt', $sut->getOperator());
        $this->assertEquals(false, $sut->hasTime());
        $this->assertSame('P5D', $sut->getDateInterval());
        $this->assertEquals('X days', $sut->getIntervalLabel());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
    public function testIsValid($expected, $options, $value, $context, $errorMessages = []): void
    {
        $errorMessages = empty($errorMessages) ? ['error' => 'message'] : $errorMessages;

        $sut = new DateCompareWithInterval();
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->isValid($value, $context));

        if (!$expected) {
            $this->assertEquals($errorMessages, $sut->getMessages());
        }
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideIsValid(): \Iterator
    {
        //context matches, field is valid
        yield [
            true,
            ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-11',
            ['other_field' =>
                ['day' => '09', 'month' => '01', 'year' => '2014'], true],
            true
        ];
        //context matches, field is invalid
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-10',
            ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::NOT_GT => "This date must be 2 days after the 'Other field'"]
        ];
        //context doesn't match, field is invalid
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-10',
            [],
            [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
        ];
        //missing context
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-10',
            [],
            [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
        ];
        //Context field partially empty
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-10',
            ['other_field' => ['day' => '', 'month' => '', 'year' => '2014']],
            [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
        ];
        //context matches value is empty
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '',
            ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::INVALID_FIELD => "Input field being compared to doesn't exist"]
        ];
        //context matches, field is valid, invalid operator gte
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'gte', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-10',
            ['other_field' => ['day' => '10', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::INVALID_OPERATOR => 'Invalid operator']
        ];
        //context matches, field has time and is valid, valid gt
        yield [
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
        ];
        //context matches, field is invalid gt
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'gt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-12',
            ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::NOT_GT => "This date must be 2 days after the 'Other field'"]
        ];
        //context matches, field is valid lt
        yield [
            true,
            ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-09',
            ['other_field' => ['day' => '11', 'month' => '01', 'year' => '2014']],
            true
        ];
        //context matches, field is invalid lt
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-08',
            ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::NOT_LT => "This date must be 2 days before 'Other field'"]
        ];
        // Invalid Interval
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'lt', 'compare_to_label' => 'Other field',
                'date_interval' => 'INVALID', 'interval_label' => '2 days'],
            '2014-01-08',
            ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::INVALID_INTERVAL => 'Invalid interval']
        ];
        //Invalid operator
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-10',
            ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::INVALID_OPERATOR => 'Invalid operator']
        ];
        //Can't compare
        yield [
            false,
            ['compare_to' => 'other_field', 'operator' => 'invalid', 'compare_to_label' => 'Other field',
                'date_interval' => 'P2D', 'interval_label' => '2 days'],
            '2014-01-',
            ['other_field' => ['day' => '09', 'month' => '01', 'year' => '2014']],
            [DateCompareWithInterval::NO_COMPARE => "Unable to compare with 'Other field'"]
        ];
    }
}
