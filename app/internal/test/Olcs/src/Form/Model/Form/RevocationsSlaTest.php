<?php


namespace OlcsTest\Form\Model\Form;


use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Radio;

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
        $this->assertFormElementType($elementHierarchy, Radio::class);
    }

    public function testApprovalSubmissionReturnedDate()
    {
        $elementHierarchy = ['fields', 'approvalSubmissionReturnedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testApprovalSubmissionPresidingTc()
    {
        $elementHierarchy = ['fields', 'approvalSubmissionPresidingTc'];
        $this->assertFormElementDynamicSelect($elementHierarchy);
    }

    public function testIorLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'iorLetterIssuedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testOperatorResponseDueDate()
    {
        $elementHierarchy = ['fields', 'operatorResponseDueDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testOperatorResponseReceivedDate()
    {
        $elementHierarchy = ['fields', 'operatorResponseReceivedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testApprovalSubmissionIssuedDate()
    {
        $elementHierarchy = ['fields', 'approvalSubmissionIssuedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testFinalSubmissionIssuedDate()
    {
        $elementHierarchy = ['fields', 'finalSubmissionIssuedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testFinalSubmissionReturnedDate()
    {
        $elementHierarchy = ['fields', 'finalSubmissionReturnedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testFinalSubmissionPresidingTc()
    {
        $elementHierarchy = ['fields', 'finalSubmissionPresidingTc'];
        $this->assertFormElementDynamicSelect($elementHierarchy);
    }

    public function testIsSubmissionRequiredForAction()
    {
        $elementHierarchy = ['fields', 'isSubmissionRequiredForAction'];
        $this->assertFormElementType($elementHierarchy, Radio::class);
    }

    public function testActionToBeTaken()
    {
        $elementHierarchy = ['fields', 'actionToBeTaken'];
        $this->assertFormElementDynamicSelect($elementHierarchy);
    }

    public function testRevocationLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'revocationLetterIssuedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }


    public function testNfaLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'nfaLetterIssuedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testWarningLetterIssuedDate()
    {
        $elementHierarchy = ['fields', 'warningLetterIssuedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testPiAgreedDate()
    {
        $elementHierarchy = ['fields', 'piAgreedDate'];
        $this->assertFormElementDate($elementHierarchy);
    }

    public function testOtherActionAgreedDate()
    {
        $elementHierarchy = ['fields', 'otherActionAgreedDate'];
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
