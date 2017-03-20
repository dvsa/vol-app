<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Types\Html;
use Zend\Form\Element\Button;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Common\Form\Elements\Validators\DateNotInFuture;
use Zend\Validator\Date;
use Zend\Validator\Identical;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Form\Elements\Types\HtmlDateTime;
use Zend\Form\Element\Radio;

/**
 * Class UserTest
 *
 * @group FormTests
 */
class UserTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\User::class;

    public function testId()
    {
        $element = [ 'id' ];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = [ 'version' ];
        $this->assertFormElementHidden($element);
    }

    public function testUserType()
    {
        $element = ['userType', 'userType'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testUserTypeTeam()
    {
        $element = ['userType', 'team'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testUserTypeCurrentTransportManagerHtml()
    {
        $element = ['userType', 'currentTransportManagerHtml'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Html::class);
    }

    public function testUserTypeCurrentTransportManager()
    {
        $element = ['userType', 'currentTransportManager'];
        $this->assertFormElementHidden($element);
    }

    public function testUserTypeCurrentTransportManagerName()
    {
        $element = ['userType', 'currentTransportManagerName'];
        $this->assertFormElementHidden($element);
    }

    public function testTransportManager()
    {
        $element = ['userType', 'transportManager'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Select::class);
    }

    public function testLocalAuthority()
    {
        $element = ['userType', 'localAuthority'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testPartnerContactDetails()
    {
        $element = ['userType', 'partnerContactDetails'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testUserTypeId()
    {
        $element = ['userType', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testLicenceNumberText()
    {
        $element = ['userType', 'licenceNumber'];
        $this->assertFormElementRequired($element, false);
    }

    public function testForename()
    {
        $element = [ 'userPersonal', 'forename' ];
        $this->assertFormElementText($element, 1, 35);
    }

    public function testFamilyName()
    {
        $element = [ 'userPersonal', 'familyName' ];
        $this->assertFormElementText($element, 1, 35);
    }

    public function testDateOfBirth()
    {
        $element = ['userPersonal', 'birthDate'];
        $this->assertFormElementValid(
            $element,
            [
                'day'   => '15',
                'month' => '06',
                'year'  => '1987',
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            [
                'day'   => 'X',
                'month' => '06',
                'year'  => '1987',
            ],
            [
                Date::INVALID_DATE
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            [
                'day'   => '15',
                'month' => '06',
                'year'  => date('Y')+10,
            ],
            [
                DateNotInFuture::IN_FUTURE,
            ]
        );

        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testUserAddress()
    {
        $element = [ 'address', 'id' ];
        $this->assertFormElementRequired($element, false);

        $element = [ 'address', 'version'];
        $this->assertFormElementRequired($element, false);

        $element = [ 'address', 'addressLine1' ];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = [ 'address', 'addressLine2' ];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = [ 'address', 'addressLine3' ];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = [ 'address', 'addressLine4' ];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = [ 'address', 'town' ];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = [ 'address', 'postcode' ];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementPostcode($element);

        $element = ['address', 'countryCode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDynamicSelect($element, false);

        $element = ['address', 'searchPostcode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testUserContactDetailsEmailAddress()
    {
        $element = ['userContactDetails', 'emailAddress'];

        $this->assertFormElementRequired($element, true);

        $this->assertFormElementValid(
            $element,
            'gaurav@valtech.co.uk',
            [
                'userContactDetails' => [
                    'emailConfirm' => 'gaurav@valtech.co.uk'
                ]
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            'gaurav@valtech.co.uk',
            [Identical::NOT_SAME],
            [
                'userContactDetails' => [
                    'emailConfirm' => 'gaurav@valtech111.co.uk'
                ]
            ]
        );

        $element = ['userContactDetails', 'emailConfirm'];
        $this->assertFormElementRequired($element, true);
    }

    public function testContactPhoneBusiness()
    {
        $element = ['userContactDetails', 'phone_business'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhoneBusinessId()
    {
        $element = ['userContactDetails', 'phone_business_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhoneBusinessVersion()
    {
        $element = ['userContactDetails', 'phone_business_version'];
        $this->assertFormElementHidden($element);
    }

    public function testContactFaxBusiness()
    {
        $element = ['userContactDetails', 'phone_fax'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactFaxBusinessId()
    {
        $element = ['userContactDetails', 'phone_fax_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactFaxBusinessVersion()
    {
        $element = ['userContactDetails', 'phone_fax_version'];
        $this->assertFormElementHidden($element);
    }

    public function testUserTypeRole()
    {
        $element = ['userType', 'role'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testTranslateToWelsh()
    {
        $element = ['userSettings', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testUserLoginId()
    {
        $element = ['userLoginSecurity', 'loginId'];
        $this->assertFormElementRequired($element, true);
    }

    public function testUserLoginCreatedOn()
    {
        $element = ['userLoginSecurity', 'createdOn'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Html::class);
    }

    public function testUserLoginLastLoggedIn()
    {
        $element = ['userLoginSecurity', 'lastLoggedInOn'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, HtmlDateTime::class);
    }

    public function testUserLoginLocked()
    {
        $element = ['userLoginSecurity', 'locked'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Html::class);
    }

    public function testUserPasswordLastReset()
    {
        $element = ['userLoginSecurity', 'passwordLastReset'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Html::class);
    }

    public function testUserLoginResetPassword()
    {
        $element = ['userLoginSecurity', 'resetPassword'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testAccountDisabled()
    {
        $element = ['userLoginSecurity', 'accountDisabled'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testAccountDisbaledDate()
    {
        $element = ['userLoginSecurity', 'disabledDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementType($element, Html::class);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
