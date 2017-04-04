<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class IrfoDetailsTest
 *
 * @group FormTests
 */
class IrfoDetailsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\IrfoDetails::class;

    public function testIdHtml()
    {
        $this->assertFormElementHtml(['fields', 'idHtml']);
    }

    public function testIrfoNationality()
    {
        $element = ['fields', 'irfoNationality'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(['address', 'searchPostcode']);
    }

    public function testAddressId()
    {
        $this->assertFormElementHidden(['address', 'id']);
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(['address', 'version']);
    }

    public function testAddressLine1()
    {
        $this->assertFormElementRequired(
            ['address', 'addressLine1'],
            false
        );
    }

    public function testAddressLine2()
    {
        $this->assertFormElementRequired(
            ['address', 'addressLine2'],
            false
        );
    }

    public function testAddressLine3()
    {
        $this->assertFormElementRequired(
            ['address', 'addressLine3'],
            false
        );
    }

    public function testAddressLine4()
    {
        $this->assertFormElementRequired(
            ['address', 'addressLine4'],
            false
        );
    }

    public function testTown()
    {
        $this->assertFormElementRequired(
            ['address', 'town'],
            false
        );
    }

    public function testPostcode()
    {
        $this->assertFormElementRequired(
            ['address', 'postcode'],
            false
        );
    }

    public function testCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['address', 'countryCode'],
            false
        );
    }

    public function testContactPhoneBusiness()
    {
        $element = ['contact', 'phone_business'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
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

    public function testContactPhoneHome()
    {
        $element = ['contact', 'phone_home'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhoneHomeId()
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

    public function testContactPhoneMobile()
    {
        $element = ['contact', 'phone_mobile'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhoneMobileId()
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

    public function testContactPhoneFax()
    {
        $element = ['contact', 'phone_fax'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhoneFaxId()
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
