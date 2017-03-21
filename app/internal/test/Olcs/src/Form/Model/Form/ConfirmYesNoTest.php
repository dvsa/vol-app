<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class ConfirmYesNoTest
 *
 * @group FormTests
 */
class ConfirmYesNoTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\ConfirmYesNo::class;

    public function testYesButton()
    {
        $element = ['form-actions', 'yes'];
        $this->assertFormElementActionButton($element);
    }

    public function testNoButton()
    {
        $element = ['form-actions', 'no'];
        $this->assertFormElementActionButton($element);
    }
}
