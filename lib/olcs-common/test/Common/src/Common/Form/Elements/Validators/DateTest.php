<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\Date;

/**
 * Test DateTest
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $expected
     * @param $value
     * @param array $errorMessages
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($expected, $value, $errorMessages = []): void
    {
        $errorMessages = empty($errorMessages) ? ['error' => 'message'] : $errorMessages;

        $sut = new Date();
        $this->assertEquals($expected, $sut->isValid($value));

        if (!$expected) {
            $this->assertEquals($errorMessages, $sut->getMessages());
        }
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function providerIsValid(): \Iterator
    {
        // valid date
        yield 'valid date' => [
            true,
            '2014-11-10',
            [Date::INVALID => 'Please select a date']
        ];
        // empty dates should be valid
        yield 'null date' => [
            true,
            null,
            [Date::INVALID => 'Please select a date']
        ];
        yield 'empty string date' => [
            false,
            '',
            [Date::INVALID => 'Please select a date']
        ];
        // other invalid dates
        yield 'impossible date' => [
            false,
            '2014-02-30',
            [Date::INVALID_DATE => 'The input does not appear to be a valid value']
        ];
        yield 'invalid string date' => [
            false,
            'invalidinputstring',
            [Date::INVALID_DATE => 'The input does not appear to be a valid value']
        ];
        // the existing behaviour from LaminasDate is 'invalid date' for other data types
        yield 'invalid object' => [
            false,
            new \StdClass(),
            [Date::INVALID_DATE => 'The input does not appear to be a valid value']
        ];
    }
}
