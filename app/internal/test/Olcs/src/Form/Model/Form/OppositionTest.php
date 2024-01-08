<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
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
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 5, 255);
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
        $this->assertFormElementIsRequired(['fields', 'validNotes'], false);
    }

    public function testIsCopied()
    {
        $element = ['fields', 'isCopied'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testIsWillingToAttendPi()
    {
        $element = ['fields', 'isWillingToAttendPi'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testIsInTime()
    {
        $element = ['fields', 'isInTime'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testIsWithdrawn()
    {
        $element = ['fields', 'isWithdrawn'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementIsRequired($element, true);
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
        $this->assertFormElementIsRequired($element, false);
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

    public function testContactPhonePrimary()
    {
        $element = ['contact', 'phone_primary'];
        $this->assertFormElementIsRequired($element, false);
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

    public function testContactPhoneSecondary()
    {
        $element = ['contact', 'phone_secondary'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testContactPhoneSecondaryId()
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
        $this->assertFormElementIsRequired(
            ['address', 'addressLine1'],
            false
        );
    }

    public function testAddressLine2()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine2'],
            false
        );
    }

    public function testAddressLine3()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine3'],
            false
        );
    }

    public function testAddressLine4()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine4'],
            false
        );
    }

    public function testTown()
    {
        $this->assertFormElementIsRequired(
            ['address', 'town'],
            false
        );
    }

    public function testPostcode()
    {
        $element = ['address', 'postcode'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['address', 'countryCode'],
            false
        );
    }

    /**
     * This doesn't perform any assertions as per the documentation for
     * assertFormElementPostcodeSearch() in AbstractFormValidationTestCase
     *
     * @doesNotPerformAssertions
     */
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
