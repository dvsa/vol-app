<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;

/**
 * Class PublicHolidayTest
 *
 * @group FormTests
 */
class PublicHolidayTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\PublicHoliday::class;

    public function testFieldsId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testFieldsAreas()
    {
        $element = ['fields', 'areas'];
        $this->assertFormElementValid($element, 'isEngland');
        $this->assertFormElementValid($element, 'isWales');
        $this->assertFormElementValid($element, 'isScotland');
        $this->assertFormElementValid($element, 'isNI');
    }

    public function testFieldsDate()
    {
        $element = ['fields', 'holidayDate'];

        $futureYear = date('Y') + 1;

        $errorMessages = [
            Date::INVALID_DATE,
        ];

        $this->assertFormElementValid(
            $element,
            [
                'day' => 1,
                'month' => '2',
                'year' => $futureYear
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            ['day' => 'X', 'month' => '1', 'year' => 1999],
            $errorMessages
        );
    }

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancelButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
