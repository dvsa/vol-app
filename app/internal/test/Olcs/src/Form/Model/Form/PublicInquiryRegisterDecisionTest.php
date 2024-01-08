<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class PublicInquiryAgreedAndLegislationTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class PublicInquiryRegisterDecisionTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\PublicInquiryRegisterDecision::class;

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

    public function testDecisions()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'decisions']
        );
    }

    public function testLicenceRevokedAtPi()
    {
        $this->assertFormElementRequired(
            ['fields', 'licenceRevokedAtPi'],
            true
        );
    }

    public function testLicenceSuspendedAtPi()
    {
        $this->assertFormElementRequired(
            ['fields', 'licenceSuspendedAtPi'],
            true
        );
    }

    public function testLicenceCurtailedAtPi()
    {
        $this->assertFormElementRequired(
            ['fields', 'licenceCurtailedAtPi'],
            true
        );
    }

    public function testTmCalledWithOperator()
    {
        $this->assertFormElementRequired(
            ['fields', 'tmCalledWithOperator'],
            true
        );
    }

    public function testecisions()
    {
        $this->assertFormElementDynamicSelect(['fields', 'tmDecisions'], true);
    }

    public function testWitnesses()
    {
        $this->assertFormElementRequired(['fields', 'witnesses'], true);
    }

    public function testDecisionDate()
    {
        $this->assertFormElementDate(['fields', 'decisionDate']);
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
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testPublish()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'publish']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
