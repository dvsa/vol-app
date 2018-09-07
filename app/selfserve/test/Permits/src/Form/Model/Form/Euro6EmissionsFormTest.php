<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator;

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
        $element = ['fields', 'emissions'];

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, "\Common\Form\Elements\InputFilters\SingleCheckbox");

        $this->assertFormElementValid($element, '1');
        $this->assertFormElementNotValid($element, '0', [Validator\Identical::NOT_SAME]);
        $this->assertFormElementNotValid($element, 'No', [Validator\Identical::NOT_SAME]);
        $this->assertFormElementNotValid($element, 'X', [Validator\Identical::NOT_SAME]);//InArray::NOT_IN_ARRAY
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }

}
