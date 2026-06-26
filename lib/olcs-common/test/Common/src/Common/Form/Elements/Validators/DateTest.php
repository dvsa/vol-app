<?php

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\Date;

/**
 * Test DateTest
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider providerIsValid
     * @param $expected
     * @param $value
     * @param array $errorMessages
     */
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
     * @return array
     */
    public function providerIsValid()
    {
        return [
            // valid date
            'valid date' => [
                true,
                '2014-11-10',
                [Date::INVALID => 'Please select a date']
            ],
            // empty dates should be valid
            'null date' => [
                true,
                null,
                [Date::INVALID => 'Please select a date']
            ],
            'empty string date' => [
                false,
                '',
                [Date::INVALID => 'Please select a date']
            ],
            // other invalid dates
            'impossible date' => [
                false,
                '2014-02-30',
                [Date::INVALID_DATE => 'The input does not appear to be a valid value']
            ],
            'invalid string date' => [
                false,
                'invalidinputstring',
                [Date::INVALID_DATE => 'The input does not appear to be a valid value']
            ],
            // the existing behaviour from LaminasDate is 'invalid date' for other data types
            'invalid object' => [
                false,
                new \StdClass(),
                [Date::INVALID_DATE => 'The input does not appear to be a valid value']
            ],
        ];
    }
}
