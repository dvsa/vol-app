<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class OppositionTest
 *
 * @group FormTests
 */
class OppositionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Opposition::class;

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testOppositionType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'oppositionType'],
            true
        );
    }

    public function testContactDetailsDescription()
    {
        $element = ['fields', 'contactDetailsDescription'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testRaisedDate()
    {
        $this->assertFormElementDate(['fields', 'raisedDate']);
    }

    public function testOutOfRepresentationDate()
    {
        $this->assertFormElementHtml(['fields', 'outOfRepresentationDate']);
    }

    public function testOutOfObjectionDate()
    {
        $this->assertFormElementHtml(['fields', 'outOfObjectionDate']);
    }

    public function testOpposerType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'opposerType'],
            true
        );
    }

    public function testIsValid()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'isValid'],
            true
        );
    }

    public function testValidNotes()
    {
        $this->assertFormElementRequired(['fields', 'validNotes'], false);
    }

    public function testIsCopied()
    {
        $element = ['fields', 'isCopied'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testIsWillingToAttendPi()
    {
        $element = ['fields', 'isWillingToAttendPi'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testIsInTime()
    {
        $element = ['fields', 'isInTime'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testIsWithdrawn()
    {
        $element = ['fields', 'isWithdrawn'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testStatus()
    {
        $this->assertFormElementDynamicSelect(['fields', 'status'], true);
    }

    public function testLicenceOperatingCentres()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'licenceOperatingCentres'],
            true
        );
    }

    public function testApplicationOperatingCentres()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'applicationOperatingCentres'],
            false
        );
    }

    public function testGrounds()
    {
        $this->assertFormElementDynamicSelect(['fields', 'grounds'], false);
    }

    public function testNotes()
    {
        $element = ['fields', 'notes'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 4000);
    }

    public function testCaseHidden()
    {
        $this->assertFormElementHidden(['fields', 'case']);
    }

    public function testPersonForename()
    {
        $this->assertFormElementAllowEmpty(['person', 'forename'], true);
    }

    public function testPersonFamilyName()
    {
        $this->assertFormElementAllowEmpty(['person', 'familyName'], true);
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

    public function testContactEmailAddress()
    {
        $this->assertFormElementEmailAddress(
            ['contact', 'emailAddress']
        );
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

    public function testSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(['address', 'searchPostcode']);
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
