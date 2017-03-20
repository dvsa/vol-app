<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
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
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['fields', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testAppealDate()
    {
        $element = ['fields', 'appealDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDate($element);
    }

    public function testDeadlineDate()
    {
        $element = ['fields', 'deadlineDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testDvsaNotified()
    {
        $element = ['fields', 'dvsaNotified'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testAppealNo()
    {
        $element = ['fields', 'appealNo'];
        $this->assertFormElementRequired($element, false);
    }

    public function testReason()
    {
        $element = ['fields', 'reason'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testOutlineGround()
    {
        $element = ['fields', 'outlineGround'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testHearingDate()
    {
        $element = ['fields', 'hearingDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testDecisionDate()
    {
        $element = ['fields', 'decisionDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersDueTcDate()
    {
        $element = ['fields', 'papersDueTcDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersSentTcDate()
    {
        $element = ['fields', 'papersSentTcDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersDueDate()
    {
        $element = ['fields', 'papersDueDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testPapersSentDate()
    {
        $element = ['fields', 'papersSentDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testOutcome()
    {
        $element = ['fields', 'outcome'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testComment()
    {
        $element = ['fields', 'comment'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testIsWithdrawn()
    {
        $element = ['fields', 'isWithdrawn'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testWithdrawnDate()
    {
        $element = ['fields', 'withdrawnDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testCase()
    {
        $element = ['fields', 'case'];
        $this->assertFormElementHidden($element);
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
