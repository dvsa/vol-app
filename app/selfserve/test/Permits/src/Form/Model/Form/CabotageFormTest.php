<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator;

/**
 * Class UserTest
 *
 * @group FormTests
 */
class CabotageFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\CabotageForm::class;

    public function testWillCabotage()
    {
        $element = ['Fields', 'WillCabotage'];

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, "\Common\Form\Elements\InputFilters\SingleCheckbox");

        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementNotValid($element, 'N', [Validator\Identical::NOT_SAME]);
        $this->assertFormElementNotValid($element, 'X', [Validator\Identical::NOT_SAME]); //[Validator\InArray::NOT_IN_ARRAY]
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }

}
