<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;

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

    public function testRequiredEuro5()
    {
        $element = ['Fields','requiredEuro5'];
        $this->assertElementExists($element);
        $this->assertFormElementType($element, Text::class);
    }

    public function testRequiredEuro6()
    {
        $element = ['Fields','requiredEuro6'];
        $this->assertElementExists($element);
        $this->assertFormElementType($element, Text::class);
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

    public function testNumVehicles()
    {
        $element = ['Fields', 'numVehicles'];
        $this->assertFormElementHidden($element);
    }
}
