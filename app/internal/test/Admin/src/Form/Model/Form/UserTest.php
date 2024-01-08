<?php

namespace AdminTest\Form\Model\Form;

use Common\Form\Elements\Types\Readonly as ReadonlyAlias;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Types\Html;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Common\Form\Elements\Validators\DateNotInFuture;
use Laminas\Validator\Date;
use Laminas\Validator\Identical;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Form\Elements\Types\HtmlDateTime;
use Laminas\Form\Element\Radio;

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
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    /**
     * This is a similar scenario to search postcode elements. These are
     * skipped deliberately. They do not implement any InputFilters and
     * validated by JS.
     *
     * @doesNotPerformAssertions
     */
    public function testApplicationTransportManagersApplication()
    {
        $elementHierarchy = ['userType', 'applicationTransportManagers'];

        $applicationTransportManagerElements = [
            'application',
            'search',
        ];

        foreach ($applicationTransportManagerElements as $element) {
            $elementToSkip = array_merge(
                $elementHierarchy,
                [$element]
            );

            self::$testedElements[implode('.', $elementToSkip)] = true;
        }
    }

    public function testUserType()
    {
        $this->assertFormElementDynamicSelect(
            ['userType', 'userType'],
            true
        );
    }

    public function testUserTypeTeam()
    {
        $this->assertFormElementDynamicSelect(
            ['userType', 'team'],
            false
        );
    }

    public function testUserTypeCurrentTransportManagerHtml()
    {
        $element = ['userType', 'currentTransportManagerHtml'];

        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, Html::class);
    }

    public function testUserTypeCurrentTransportManager()
    {
        $this->assertFormElementHidden(
            ['userType', 'currentTransportManager']
        );
    }

    public function testUserTypeCurrentTransportManagerName()
    {
        $this->assertFormElementHidden(
            ['userType', 'currentTransportManagerName']
        );
    }

    public function testTransportManager()
    {
        $element = ['userType', 'transportManager'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, Select::class);
    }

    public function testLocalAuthority()
    {
        $this->assertFormElementDynamicSelect(
            ['userType', 'localAuthority'],
            true
        );
    }

    public function testPartnerContactDetails()
    {
        $this->assertFormElementDynamicSelect(
            ['userType', 'partnerContactDetails'],
            true
        );
    }

    public function testUserTypeId()
    {
        $this->assertFormElementHidden(
            ['userType', 'id']
        );
    }

    public function testLicenceNumberText()
    {
        $element = ['userType', 'licenceNumber'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testForename()
    {
        $this->assertFormElementText(
            ['userPersonal', 'forename'],
            1,
            35
        );
    }

    public function testFamilyName()
    {
        $this->assertFormElementText(
            ['userPersonal', 'familyName'],
            1,
            35
        );
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
                Date::INVALID_DATE,
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            [
                'day'   => '15',
                'month' => '06',
                'year'  => date('Y') + 10,
            ],
            [
                DateNotInFuture::IN_FUTURE,
            ]
        );

        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testUserAddress()
    {
        $element = ['address', 'id'];
        $this->assertFormElementIsRequired($element, false);

        $element = ['address', 'version'];
        $this->assertFormElementIsRequired($element, false);

        $element = ['address', 'addressLine1'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = ['address', 'addressLine2'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = ['address', 'addressLine3'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = ['address', 'addressLine4'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = ['address', 'town'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 1);

        $element = ['address', 'postcode'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementPostcode($element);

        $element = ['address', 'countryCode'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementDynamicSelect($element, false);

        $element = ['address', 'searchPostcode'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testUserContactDetailsEmailAddress()
    {
        $element = ['userContactDetails', 'emailAddress'];

        $this->assertFormElementIsRequired($element, true);

        $this->assertFormElementValid(
            $element,
            'gaurav@valtech.co.uk',
            [
                'userContactDetails' => [
                    'emailConfirm' => 'gaurav@valtech.co.uk',
                ],
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            'gaurav@valtech.co.uk',
            [Identical::NOT_SAME],
            [
                'userContactDetails' => [
                    'emailConfirm' => 'gaurav@valtech111.co.uk',
                ],
            ]
        );

        $element = ['userContactDetails', 'emailConfirm'];
        $this->assertFormElementIsRequired($element, true);
    }

    public function testContactPhonePrimary()
    {
        $element = ['userContactDetails', 'phone_primary'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactPhonePrimaryId()
    {
        $element = ['userContactDetails', 'phone_primary_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactPhonePrimaryVersion()
    {
        $element = ['userContactDetails', 'phone_primary_version'];
        $this->assertFormElementHidden($element);
    }

    public function testContactSecondaryBusiness()
    {
        $element = ['userContactDetails', 'phone_secondary'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPhone($element);
    }

    public function testContactSecondaryBusinessId()
    {
        $element = ['userContactDetails', 'phone_secondary_id'];
        $this->assertFormElementHidden($element);
    }

    public function testContactSecondaryBusinessVersion()
    {
        $element = ['userContactDetails', 'phone_secondary_version'];
        $this->assertFormElementHidden($element);
    }

    public function testUserTypeRole()
    {
        $this->assertFormElementDynamicSelect(
            ['userType', 'role'],
            true
        );
    }

    public function testTranslateToWelsh()
    {
        $element = ['userSettings', 'translateToWelsh'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testOsType()
    {
        $element = ['userSettings', 'osType'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUserLoginId()
    {
        $this->assertFormElementIsRequired(
            ['userLoginSecurity', 'loginId'],
            false
        );
    }

    public function testUserLoginCreatedOn()
    {
        $element = ['userLoginSecurity', 'createdOn'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, HtmlDateTime::class);
    }

    public function testUserLoginLastLoggedIn()
    {
        $element = ['userLoginSecurity', 'lastLoggedInOn'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, HtmlDateTime::class);
    }

    public function testUserLoginLocked()
    {
        $element = ['userLoginSecurity', 'locked'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, ReadonlyAlias::class);
    }

    public function testUserPasswordLastReset()
    {
        $element = ['userLoginSecurity', 'passwordLastReset'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, ReadonlyAlias::class);
    }

    public function testUserLoginResetPassword()
    {
        $element = ['userLoginSecurity', 'resetPassword'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testAccountDisabled()
    {
        $element = ['userLoginSecurity', 'accountDisabled'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testAccountDisbaledDate()
    {
        $element = ['userLoginSecurity', 'disabledDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementType($element, HtmlDateTime::class);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
