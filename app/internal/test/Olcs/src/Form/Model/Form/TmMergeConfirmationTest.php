<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TmMergeConfirmationTest
 *
 * @group FormTests
 */
class TmMergeConfirmationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TmMergeConfirmation::class;

    public function testMessages()
    {
        $element = ['messages', 'message'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementHtml($element);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    public function testTmId()
    {
        $this->assertFormElementHidden(['toTmId']);
    }

    public function testChangeUserConfirm()
    {
        $this->assertFormElementHidden(['changeUserConfirm']);
    }
}
