<?php

namespace PermitsTest\Form\Permits\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class EligibilityFormTest
 *
 * @group FormTests
 */
class EligibilityFormTest extends AbstractFormValidationTestCase
{

    protected $formName = \Permits\Form\EligibilityForm::class;

    public function testId()
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testWillCabotage()
    {
        $element = ['fields', 'willCabotage'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicRadio($element, false);
    }

    public function testMeetsEuro6()
    {
        $element = ['fields', 'meetsEuro6'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicRadio($element, false);
    }

    public function testCountry()
    {
        $element = ['fields', 'country'];
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