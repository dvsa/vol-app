<?php

namespace PermitsTest\Form\Model\Form;

use Common\Form\Elements\Custom\EcmtNoOfPermitsCombinedTotalElement;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Submit;

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

    public function testCombinedTotalChecker()
    {
        $element = ['Fields','combinedTotalChecker'];
        $this->assertElementExists($element);
        $this->assertFormElementType($element, EcmtNoOfPermitsCombinedTotalElement::class);
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
