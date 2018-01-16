<?php


namespace OlcsTest\Form\Model\Form;


use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

class RevocationsSlaTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\RevocationsSla::class;

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testisSubmissionRequiredForApproval()
    {
        $this->assertFormElementDynamicRadio(['fields', 'isSubmissionRequiredForApproval', false]);
    }
}
