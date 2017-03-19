<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator\Date;
use Zend\Validator\InArray;

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
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
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

        $futureYear = date('Y')+1;

        $errorMessages = [
            Date::INVALID_DATE,
        ];

        $this->assertFormElementValid($element, ['day' => 1, 'month' => '2', 'year' => $futureYear]);
        $this->assertFormElementNotValid($element, ['day' => 'X', 'month' => '1', 'year' => 1999], $errorMessages);
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
