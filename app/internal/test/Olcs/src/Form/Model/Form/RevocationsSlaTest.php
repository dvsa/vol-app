<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
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

    public function testApprovalSubmissionReturnedDate()
    {
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

    public function testIorLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'iorLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testOperatorResponseDueDate()
    {
        $elementHierarchy = ['fields', 'operatorResponseDueDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testOperatorResponseReceivedDate()
    {
        $elementHierarchy = ['fields', 'operatorResponseReceivedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testApprovalSubmissionIssuedDate()
    {
        $elementHierarchy = ['fields', 'approvalSubmissionIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testFinalSubmissionIssuedDate()
    {
        $elementHierarchy = ['fields', 'finalSubmissionIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testFinalSubmissionReturnedDate()
    {
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

    public function testRevocationLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'revocationLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testNfaLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'nfaLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testWarningLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'warningLetterIssuedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testPiAgreedDate()
    {
        $elementHierarchy = ['fields', 'piAgreedDate'];
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementIsRequired($elementHierarchy, false);
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testOtherActionAgreedDate()
    {
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
