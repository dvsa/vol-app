<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class UnlicensedOperatorTest
 *
 * @group FormTests
 */
class UnlicensedOperatorTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\UnlicensedOperator::class;

    public function testOperatorDetailsId()
    {
        $this->assertFormElementHidden(['operator-details', 'id']);
    }

    public function testOperatorDetailsVersion()
    {
        $this->assertFormElementHidden(['operator-details', 'version']);
    }

    public function testOperatorDetailsName()
    {
        $this->assertFormElementRequired(['operator-details', 'name'], true);
    }

    public function testOperatorDetailsOperatorType()
    {
        $this->assertFormElementRequired(
            ['operator-details', 'operatorType'],
            true
        );
    }

    public function testOperatorDetailsTrafficArea()
    {
        $this->assertFormElementDynamicSelect(
            ['operator-details', 'trafficArea'],
            true
        );
    }

    public function testOperatorContactDetailsId()
    {
        $this->assertFormElementHidden(
            ['operator-details', 'contactDetailsId']
        );
    }

    public function testOperatorContactDetailsVersion()
    {
        $this->assertFormElementHidden(
            ['operator-details', 'contactDetailsVersion']
        );
    }

    public function testCorrespondenceAddressId()
    {
        $this->assertFormElementHidden(['correspondenceAddress', 'id']);
    }

    public function testCorrespondenceAddressVersion()
    {
        $this->assertFormElementHidden(['correspondenceAddress', 'version']);
    }

    public function testCorrespondenceAddressLine1()
    {
        $this->assertFormElementRequired(
            ['correspondenceAddress', 'addressLine1'],
            false
        );
    }

    public function testCorrespondenceAddressLine2()
    {
        $this->assertFormElementRequired(
            ['correspondenceAddress', 'addressLine2'],
            false
        );
    }

    public function testCorrespondenceAddressLine3()
    {
        $this->assertFormElementRequired(
            ['correspondenceAddress', 'addressLine3'],
            false
        );
    }

    public function testCorrespondenceAddressLine4()
    {
        $this->assertFormElementRequired(
            ['correspondenceAddress', 'addressLine4'],
            false
        );
    }

    public function testCorrespondenceTown()
    {
        $this->assertFormElementRequired(
            ['correspondenceAddress', 'town'],
            false
        );
    }

    public function testCorrespondencePostcode()
    {
        $this->assertFormElementRequired(
            ['correspondenceAddress', 'postcode'],
            false
        );
    }

    public function testContactPhonePrimary()
    {
        $element = ['contact', 'phone_primary'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactPhonePrimaryId()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_primary_id']
        );
    }

    public function testContactPhonePrimaryVersion()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_primary_version']
        );
    }

    public function testContactSecondary()
    {
        $element = ['contact', 'phone_secondary'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactSecondaryId()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_secondary_id']
        );
    }

    public function testContactPhoneSecondaryVersion()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_secondary_version']
        );
    }

    public function testContactEmail()
    {
        $this->assertFormElementEmailAddress(
            ['contact', 'email']
        );
    }

    public function testIsExempt()
    {
        $element = ['isExempt'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testSave()
    {
        $this->assertFormElementActionButton(['form-actions', 'save']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
