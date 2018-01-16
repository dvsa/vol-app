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
        $element = ['fields', 'isSubmissionRequiredForApproval'];
        $this->assertFormElementType($element, Radio::class);
    }

    public function testApprovalSubmissionReturnedDate()
    {
        $this->assertFormElementDate(['fields', 'approvalSubmissionReturnedDate']);
    }

    public function testApprovalSubmissionPresidingTc()
    {
        $this->assertFormElementDynamicSelect(['fields', 'approvalSubmissionPresidingTc']);
    }

    public function testIorLetterIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'iorLetterIssuedDate']);
    }

    public function testOperatorResponseDueDate()
    {
        $this->assertFormElementDate(['fields', 'operatorResponseDueDate']);
    }

    public function testOperatorResponseReceivedDate()
    {
        $this->assertFormElementDate(['fields', 'operatorResponseReceivedDate']);
    }

    public function testApprovalSubmissionIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'approvalSubmissionIssuedDate']);
    }

    public function testFinalSubmissionIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'finalSubmissionIssuedDate']);
    }

    public function testFinalSubmissionReturnedDate()
    {
        $this->assertFormElementDate(['fields', 'finalSubmissionReturnedDate']);
    }

    public function testFinalSubmissionPresidingTc()
    {
        $this->assertFormElementDynamicSelect(['fields', 'finalSubmissionPresidingTc']);
    }

    public function testIsSubmissionRequiredForAction()
    {
        $element = ['fields', 'isSubmissionRequiredForAction'];
        $this->assertFormElementType($element, Radio::class);
    }

    public function testActionToBeTaken()
    {
        $this->assertFormElementDynamicSelect(['fields', 'actionToBeTaken']);
    }

    public function testRevocationLetterIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'revocationLetterIssuedDate']);
    }


    public function testNfaLetterIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'nfaLetterIssuedDate']);
    }

    public function testWarningLetterIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'warningLetterIssuedDate']);
    }

    public function testPiAgreedDate()
    {
        $this->assertFormElementDate(['fields', 'piAgreedDate']);
    }

    public function testOtherActionAgreedDate()
    {
        $this->assertFormElementDate(['fields', 'otherActionAgreedDate']);
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
