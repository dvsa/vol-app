<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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

    public function testContactPhoneBusiness()
    {
        $element = ['contact', 'phone_business'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactPhoneBusinessId()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_business_id']
        );
    }

    public function testContactPhoneBusinessVersion()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_business_version']
        );
    }

    public function testContactHome()
    {
        $element = ['contact', 'phone_home'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactHomeId()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_home_id']
        );
    }

    public function testContactPhoneHomeVersion()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_home_version']
        );
    }

    public function testContactMobile()
    {
        $element = ['contact', 'phone_mobile'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactMobileId()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_mobile_id']
        );
    }

    public function testContactPhoneMobileVersion()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_mobile_version']
        );
    }

    public function testContactFax()
    {
        $element = ['contact', 'phone_fax'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactFaxId()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_fax_id']
        );
    }

    public function testContactPhoneFaxVersion()
    {
        $this->assertFormElementHidden(
            ['contact', 'phone_fax_version']
        );
    }

    public function testContactEmail()
    {
        $this->assertFormElementEmailAddress(
            ['contact', 'email']
        );
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
