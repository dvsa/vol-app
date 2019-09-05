<?php

namespace PermitsTest\Form\Model\Form;

use Common\Form\Element\DynamicMultiCheckbox;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;

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
        $element = ['fields','restrictedCountries'];

        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testRestrictedCountriesList()
    {
        $element = ['fields', 'yesContent', 'restrictedCountriesList'];

        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, DynamicMultiCheckbox::class);
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, Submit::class);
    }

    public function testSaveAndReturn()
    {
        $element = ['Submit', 'SaveAndReturnButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, Submit::class);
    }
}
