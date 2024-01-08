<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class ConfirmTest
 *
 * @group FormTests
 */
class ConfirmTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Confirm::class;

    public function testCustom()
    {
        $element = ['custom'];
        $this->assertFormElementHidden($element);
    }

    public function testConfirm()
    {
        $element = ['form-actions', 'confirm'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
