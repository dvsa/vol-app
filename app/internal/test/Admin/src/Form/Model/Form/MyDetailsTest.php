<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Validators\DateNotInFuture;
use Laminas\Validator\EmailAddress;
use Common\Form\Elements\Validators\EmailConfirm;

/**
 * Class MyDetailsTest
 *
 * @group FormTests
 */
class MyDetailsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\MyDetails::class;

    public function testTeam()
    {
        $this->assertFormElementDynamicSelect(['userDetails', 'team']);
    }

    public function testTitle()
    {
        $this->assertFormElementDynamicSelect(['person', 'title']);
    }

    public function testForename()
    {
        $element = ['person', 'forename'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testFamilyName()
    {
        $element = ['person', 'familyName'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testDateOfBirth()
    {
        $element = ['person', 'birthDate'];
        $this->assertFormElementNotValid(
            $element,
            [
                'day' => '15',
                'month' => '06',
                'year' => '2060',
            ],
            [DateNotInFuture::IN_FUTURE]
        );
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testEmailAddress()
    {
        $element = ['userContact', 'emailAddress'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);

        $this->assertFormElementValid(
            $element,
            'valid@email.com',
            ['userContact' => ['emailConfirm' => 'valid@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'valid@email.com',
            EmailConfirm::NOT_SAME,
            ['userContact' => ['emailConfirm' => 'other@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'invalid',
            [
                EmailAddress::INVALID_FORMAT,
                EmailConfirm::NOT_SAME,
            ],
            ['userContact' => ['emailConfirm' => 'other@email.com']]
        );
    }

    public function testEmailConfirm()
    {
        $element = ['userContact', 'emailConfirm'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element);
    }

    public function testContactPhonePrimary()
    {
        $element = ['userContact', 'phone_primary'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhonePrimaryId()
    {
        $element = ['userContact', 'phone_primary_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhonePrimaryVersion()
    {
        $element = ['userContact', 'phone_primary_version'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhoneSecondary()
    {
        $element = ['userContact', 'phone_secondary'];
        $this->assertFormElementIsRequired($element, false);
    }

    public function testContactPhoneSecondaryId()
    {
        $element = ['userContact', 'phone_secondary_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhoneSecondaryVersion()
    {
        $element = ['userContact', 'phone_secondary_version'];
        $this->assertFormElementHidden($element);
    }

    public function testOfficeAddressId()
    {
        $element = ['officeAddress', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testOfficeAddressVersion()
    {
        $element = ['officeAddress', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testOfficeAddressSearchPostcode()
    {
        $element = ['officeAddress', 'searchPostcode'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testOfficeAddressLine1()
    {
        $element = ['officeAddress', 'addressLine1'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testOfficeAddressLine2()
    {
        $element = ['officeAddress', 'addressLine2'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testOfficeAddressLine3()
    {
        $element = ['officeAddress', 'addressLine3'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 100);
    }

    public function testOfficeAddressLine4()
    {
        $element = ['officeAddress', 'addressLine4'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testOfficeAddressTown()
    {
        $element = ['officeAddress', 'town'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 30);
    }

    public function testOfficeAddressPostcode()
    {
        $element = ['officeAddress', 'postcode'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testOfficeAddressCountryCode()
    {
        $element = ['officeAddress', 'countryCode'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testTranslateToWelsh()
    {
        $element = ['userSettings', 'translateToWelsh'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
    }

    public function testOsType()
    {
        $element = ['userSettings', 'osType'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
