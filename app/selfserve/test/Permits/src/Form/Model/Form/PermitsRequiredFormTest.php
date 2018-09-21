<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * PermitsRequiredFormtest
 *
 * @group FormTests
 */
class PermitsRequiredFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\PermitsRequiredForm::class;

    public function testPermitsRequired()
    {
        $this->markTestSkipped();
        $element = ['Fields','PermitsRequired'];

        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, "Zend\Form\Element\Number");
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }

}
