<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class AppealTest
 *
 * @group FormTests
 */
class AppealTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Appeal::class;

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testAppealDate()
    {
        $element = ['fields', 'appealDate'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDate($element);
    }

    public function testDeadlineDate()
    {
        $element = ['fields', 'deadlineDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testDvsaNotified()
    {
        $element = ['fields', 'dvsaNotified'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testAppealNo()
    {
        $this->assertFormElementIsRequired(['fields', 'appealNo'], false);
    }

    public function testReason()
    {
        $element = ['fields', 'reason'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testOutlineGround()
    {
        $element = ['fields', 'outlineGround'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testHearingDate()
    {
        $element = ['fields', 'hearingDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testDecisionDate()
    {
        $element = ['fields', 'decisionDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersDueTcDate()
    {
        $element = ['fields', 'papersDueTcDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersSentTcDate()
    {
        $element = ['fields', 'papersSentTcDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersDueDate()
    {
        $element = ['fields', 'papersDueDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersSentDate()
    {
        $element = ['fields', 'papersSentDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testOutcome()
    {
        $this->assertFormElementDynamicSelect(['fields', 'outcome'], false);
    }

    public function testComment()
    {
        $element = ['fields', 'comment'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testIsWithdrawn()
    {
        $element = ['fields', 'isWithdrawn'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testWithdrawnDate()
    {
        $element = ['fields', 'withdrawnDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementValid($element, '2017-01-01');
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
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
