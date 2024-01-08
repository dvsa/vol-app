<?php

namespace OlcsTest\Form\Model\Form;

use Common\Validator\Date as CommonDateValidator;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Date as LaminasDateValidator;

/**
 * Class SubmissionSendToTest
 *
 * @group FormTests
 */
class SubmissionSendToTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SubmissionSendTo::class;

    public function testRecipientUser()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'recipientUser'],
            true
        );
    }

    public function testSenderUser()
    {
        $this->assertFormElementHidden(['fields', 'senderUser']);
    }

    public function testUrgent()
    {
        $this->assertFormElementRequired(['fields', 'urgent'], true);
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
