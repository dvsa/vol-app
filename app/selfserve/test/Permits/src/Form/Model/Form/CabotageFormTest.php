<?php

namespace PermitsTest\Form\Model\Form;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Submit;
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

    public function testWontCabotage()
    {
        $element = ['fields', 'cabotage'];

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, SingleCheckbox::class);

        $this->assertFormElementValid($element, '1');
        $this->assertFormElementNotValid($element, '0', [Validator\Identical::NOT_SAME]);
        $this->assertFormElementNotValid($element, 'No', [Validator\Identical::NOT_SAME]);
        $this->assertFormElementNotValid($element, 'X', [Validator\Identical::NOT_SAME]); //[Validator\InArray::NOT_IN_ARRAY]
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
