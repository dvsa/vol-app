<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $this->assertFormElementActionButton(['form-actions', 'yes']);
    }

    public function testNoButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'no']);
    }
}
