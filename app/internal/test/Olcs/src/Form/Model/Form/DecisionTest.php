<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
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
        $element = ['submissionActionType'];
        $this->assertFormElementHidden($element);
    }

    public function testSenderUser()
    {
        $element = ['senderUser'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmission()
    {
        $element = ['submission'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testActionTypes()
    {
        $element = ['main', 'actionTypes'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testPiReasons()
    {
        $element = ['main', 'piReasons'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testRecipientUser()
    {
        $element = ['main', 'recipientUser'];
        $this->assertFormElementDynamicSelect($element, true);
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
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
