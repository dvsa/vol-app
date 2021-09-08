<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

class RevocationsSlaTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\RevocationsSla::class;

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testisSubmissionRequiredForApproval()
    {
        $elementHierarchy = ['fields', 'isSubmissionRequiredForApproval'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementType($elementHierarchy, Radio::class);
        $this->assertEquals(0, $this->sut->getData()['fields']['isSubmissionRequiredForApproval']);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testApprovalSubmissionReturnedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'approvalSubmissionReturnedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testApprovalSubmissionPresidingTc()
    {
        $elementHierarchy = ['fields', 'approvalSubmissionPresidingTc'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDynamicSelect($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testIorLetterIssuedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'iorLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testOperatorResponseDueDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'operatorResponseDueDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testOperatorResponseReceivedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'operatorResponseReceivedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testApprovalSubmissionIssuedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'approvalSubmissionIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testFinalSubmissionIssuedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'finalSubmissionIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testFinalSubmissionReturnedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'finalSubmissionReturnedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testFinalSubmissionPresidingTc()
    {
        $elementHierarchy = ['fields', 'finalSubmissionPresidingTc'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDynamicSelect($elementHierarchy);
    }

    public function testIsSubmissionRequiredForAction()
    {
        $elementHierarchy = ['fields', 'isSubmissionRequiredForAction'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementType($elementHierarchy, Radio::class);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertEquals(0, $this->sut->getData()['fields']['isSubmissionRequiredForAction']);
    }

    public function testActionToBeTaken()
    {
        $elementHierarchy = ['fields', 'actionToBeTaken'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDynamicSelect($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testRevocationLetterIssuedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'revocationLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testNfaLetterIssuedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'nfaLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testWarningLetterIssuedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'warningLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testPiAgreedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'piAgreedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testOtherActionAgreedDate()
    {
        $this->markTestSkipped();
        $elementHierarchy = ['fields', 'otherActionAgreedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testSubmit()
    {
        $elementHierarchy = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($elementHierarchy);
    }

    public function testCancel()
    {
        $elementHierarchy = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($elementHierarchy);
    }
}
