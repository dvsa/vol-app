<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class UserTest
 *
 * @group FormTests
 */
class RestrictedCountriesFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\RestrictedCountriesForm::class;

    public function testRestrictedCountries()
    {
        $element = ['Fields','restrictedCountries'];

        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, "Zend\Form\Element\Radio");
    }

    public function testRestrictedCountriesList()
    {
        $this->markTestSkipped();
        $element = ['Fields','restrictedCountriesList'];

        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, "Zend\Form\Element\DynamicMultiCheckbox");
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }
}
