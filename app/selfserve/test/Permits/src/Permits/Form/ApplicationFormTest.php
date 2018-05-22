<?php

namespace PermitsTest\Form\Permits\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class ApplicationFormTest
 *
 * @group FormTests
 */
class ApplicationFormTest extends AbstractFormValidationTestCase
{

    protected $formName = \Permits\Form\ApplicationForm::class;

    public function testId()
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testDeliveryCountries()
    {
        $element = ['fields', 'deliveryCountries'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
    }

    public function testNumberOfTrips()
    {
        $element = ['fields', 'numberOfTrips'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element);
    }

    public function testSector()
    {
        $element = ['fields', 'sector'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

}