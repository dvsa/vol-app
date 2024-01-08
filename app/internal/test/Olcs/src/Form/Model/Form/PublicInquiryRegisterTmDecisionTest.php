<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class PublicInquiryRegisterTmDecisionTest
 *
 * @group FormTests
 */
class PublicInquiryRegisterTmDecisionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\PublicInquiryRegisterTmDecision::class;

    public function testDecidedByTc()
    {
        $this->assertFormElementDynamicSelect(['fields', 'decidedByTc'], true);
    }

    public function testDecidedByTcRole()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'decidedByTcRole'],
            true
        );
    }

    public function testDecisionDate()
    {
        $this->assertFormElementDate(['fields', 'decisionDate']);
    }

    public function testDecisions()
    {
        $this->assertFormElementDynamicSelect(['fields', 'decisions'], true);
    }

    public function testWitnesses()
    {
        $this->assertFormElementRequired(['fields', 'witnesses'], true);
    }

    public function testNotificationDate()
    {
        $this->assertFormElementDate(['fields', 'notificationDate']);
    }

    public function testDefinition()
    {
        $this->assertFormElementDynamicSelect(['fields', 'definition'], false);
    }

    public function testDecisionNotes()
    {
        $this->assertFormElementRequired(['fields', 'decisionNotes'], false);
    }

    public function testPubType()
    {
        $this->assertFormElementRequired(['fields', 'pubType'], true);
    }

    public function testTrafficAreas()
    {
        $this->assertFormElementRequired(['fields', 'trafficAreas'], true);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
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

    public function testPublish()
    {
        $this->assertFormElementActionButton(['form-actions', 'publish']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
