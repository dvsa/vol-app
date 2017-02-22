<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class UserRegistrationAddressTest
 *
 * @group FormTests
 */
class UserRegistrationAddressTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\UserRegistrationAddress::class;

    public function testLoginId()
    {
        $element = ['fields', 'loginId'];
        $this->assertFormElementHidden($element);
    }

    public function testForename()
    {
        $element = ['fields', 'forename'];
        $this->assertFormElementHidden($element);
    }

    public function testFamilyName()
    {
        $element = ['fields', 'familyName'];
        $this->assertFormElementHidden($element);
    }

    public function testEmailAddress()
    {
        $element = ['fields', 'emailAddress'];
        $this->assertFormElementHidden($element);
    }

    public function testEmailConfirm()
    {
        $element = ['fields', 'emailConfirm'];
        $this->assertFormElementHidden($element);
    }

    public function testIsLicenceHolder()
    {
        $element = ['fields', 'isLicenceHolder'];
        $this->assertFormElementHidden($element);
    }

    public function testLicenceNumber()
    {
        $element = ['fields', 'licenceNumber'];
        $this->assertFormElementHidden($element);
    }

    public function testOrganisationName()
    {
        $element = ['fields', 'organisationName'];
        $this->assertFormElementHidden($element);
    }

    public function testBusinessType()
    {
        $element = ['fields', 'businessType'];
        $this->assertFormElementHidden($element);
    }

    public function testTranslateToWelsh()
    {
        $element = ['fields', 'translateToWelsh'];
        $this->assertFormElementHidden($element);
    }

    public function testTermsAgreed()
    {
        $element = ['fields', 'termsAgreed'];
        $this->assertFormElementHidden($element);
    }

    public function testPostAccount()
    {
        $element = ['form-actions', 'postAccount'];
        $this->assertFormElementActionButton($element);
    }
}
