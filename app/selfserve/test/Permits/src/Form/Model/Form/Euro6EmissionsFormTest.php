<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class Euro6EmissionsFormTest
 *
 * @group FormTests
 */
class Euro6EmissionsFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\Euro6EmissionsForm::class;

    public function testMeetsEuro6()
    {
        $element = ['Fields', 'MeetsEuro6'];

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);

        //Not sure the bellow would work ($element isn't an object?)
        $this->assertAttributeEquals(
                    "form-control form-control--checkbox form-control--advanced",
            "class",
                              $element
        );
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }

}
