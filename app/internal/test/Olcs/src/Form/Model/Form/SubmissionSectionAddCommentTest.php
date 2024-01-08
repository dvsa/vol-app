<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class SubmissionSectionAddCommentTest
 *
 * @group FormTests
 */
class SubmissionSectionAddCommentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SubmissionSectionAddComment::class;

    public function testComment()
    {
        $this->assertFormElementRequired(['fields', 'comment'], true);
    }

    public function testSubmissionSection()
    {
        $this->assertFormElementHidden(['fields', 'submissionSection']);
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
