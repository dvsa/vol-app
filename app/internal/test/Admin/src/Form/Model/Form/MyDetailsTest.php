<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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

    public function testLoginId()
    {
        $element = ['userDetails', 'loginId'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementUsername($element);
    }

    public function testTeam()
    {
        $element = ['userDetails', 'team'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testTitle()
    {
        $element = ['person', 'title'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testForename()
    {
        $element = ['person', 'forename'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testFamilyName()
    {
        $element = ['person', 'familyName'];
        $this->assertFormElementRequired($element, true);
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
            [ \Common\Form\Elements\Validators\DateNotInFuture::IN_FUTURE ]
        );
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testEmailAddress()
    {
        $element = ['userContact', 'emailAddress'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);

        $this->assertFormElementValid(
            $element,
            'valid@email.com',
            ['userContact' => ['emailConfirm' => 'valid@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'valid@email.com',
            \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ['userContact' => ['emailConfirm' => 'other@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'invalid',
            [
                \Dvsa\Olcs\Transfer\Validators\EmailAddress::INVALID_FORMAT,
                \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ],
            ['userContact' => ['emailConfirm' => 'other@email.com']]
        );
    }

    public function testEmailConfirm()
    {
        $element = ['userContact', 'emailConfirm'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element);
    }

    public function testContactPhoneBusiness()
    {
        $element = ['userContact', 'phone_business'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhoneBusinessId()
    {
        $element = ['userContact', 'phone_business_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhoneBusinessVersion()
    {
        $element = ['userContact', 'phone_business_version'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhoneFax()
    {
        $element = ['userContact', 'phone_fax'];
        $this->assertFormElementRequired($element, false);
    }

    public function testContactPhoneFaxId()
    {
        $element = ['userContact', 'phone_fax_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhoneFaxVersion()
    {
        $element = ['userContact', 'phone_fax_version'];
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
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testOfficeAddressLine1()
    {
        $element = ['officeAddress', 'addressLine1'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testOfficeAddressLine2()
    {
        $element = ['officeAddress', 'addressLine2'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testOfficeAddressLine3()
    {
        $element = ['officeAddress', 'addressLine3'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 100);
    }

    public function testOfficeAddressLine4()
    {
        $element = ['officeAddress', 'addressLine4'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testOfficeAddressTown()
    {
        $element = ['officeAddress', 'town'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 30);
    }

    public function testOfficeAddressPostcode()
    {
        $element = ['officeAddress', 'postcode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testOfficeAddressCountryCode()
    {
        $element = ['officeAddress', 'countryCode'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testTranslateToWelsh()
    {
        $element = ['userSettings', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
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
