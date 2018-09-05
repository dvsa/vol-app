<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator;

/**
 * Class DeclineApplicationFormTest
 *
 * @group FormTests
 */
class DeclineApplicationFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\DeclineApplicationForm::class;

    public function testDeclineApplicationFieldset()
    {
        $element = ['fields', 'DeclineApplicationFieldset'];

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, "\Common\Form\Elements\InputFilters\SingleCheckbox");

        $this->assertFormElementValid($element, '1');
        $this->assertFormElementNotValid($element, 'No', [Validator\Identical::NOT_SAME]);
        $this->assertFormElementNotValid($element, 'X', [Validator\Identical::NOT_SAME]);
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }

}
