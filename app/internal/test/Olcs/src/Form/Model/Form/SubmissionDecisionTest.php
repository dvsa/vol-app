<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class SubmissionDecisionTest
 *
 * @group FormTests
 */
class SubmissionDecisionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SubmissionDecision::class;

    public function testActionTypes()
    {
        $this->assertFormElementDynamicSelect(['fields', 'actionTypes'], true);
    }

    public function testReasons()
    {
        $this->assertFormElementDynamicSelect(['fields', 'reasons'], true);
    }

    public function testComment()
    {
        $this->assertFormElementRequired(['fields', 'comment'], true);
    }

    public function testIsDecision()
    {
        $this->assertFormElementHidden(['fields', 'isDecision']);
    }

    public function testSubmission()
    {
        $this->assertFormElementHidden(['fields', 'submission']);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
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
