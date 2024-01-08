<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class DecisionTest
 *
 * @group FormTests
 */
class DecisionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Decision::class;

    public function testSubmissionActionType()
    {
        $this->assertFormElementHidden(['submissionActionType']);
    }

    public function testSenderUser()
    {
        $this->assertFormElementHidden(['senderUser']);
    }

    public function testSubmission()
    {
        $this->assertFormElementHidden(['submission']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testActionTypes()
    {
        $this->assertFormElementDynamicSelect(
            ['main', 'actionTypes'],
            true
        );
    }

    public function testPiReasons()
    {
        $this->assertFormElementDynamicSelect(
            ['main', 'piReasons'],
            true
        );
    }

    public function testRecipientUser()
    {
        $this->assertFormElementDynamicSelect(
            ['main', 'recipientUser'],
            true
        );
    }

    public function testComment()
    {
        $element = ['main', 'comment'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testUrgent()
    {
        $element = ['main', 'urgent'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
