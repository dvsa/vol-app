<?php

namespace PermitsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;

/**
 * Class TripsFormTest
 *
 * @group FormTests
 */
class TripsFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\TripsForm::class;

    public function testTripsAbroad()
    {
        $element = ['Fields','tripsAbroad'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
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

    public function testIntensityWarning()
    {
        $element = ['Fields', 'intensityWarning'];
        $this->assertFormElementHidden($element);
    }

    public function testPermitsRequired()
    {
        $element = ['Fields', 'permitsRequired'];
        $this->assertFormElementHidden($element);
    }
}
