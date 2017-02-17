<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class UserRegistrationTest
 *
 * @group FormTests
 */
class UserRegistrationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\UserRegistration::class;

    public function testLoginId()
    {
        $element = ['fields', 'loginId'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementUsername($element);
    }

    public function testForename()
    {
        $element = ['fields', 'forename'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testFamilyName()
    {
        $element = ['fields', 'familyName'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testEmailAddress()
    {
        $element = ['fields', 'emailAddress'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);

        $this->assertFormElementValid(
            $element,
            'valid@email.com',
            ['fields' => ['emailConfirm' => 'valid@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'valid@email.com',
            \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ['fields' => ['emailConfirm' => 'other@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'invalid',
            [
                \Dvsa\Olcs\Transfer\Validators\EmailAddress::INVALID_FORMAT,
                \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ],
            ['fields' => ['emailConfirm' => 'other@email.com']]
        );
    }

    public function testEmailConfirm()
    {
        $element = ['fields', 'emailConfirm'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element);
    }

    public function testIsLicenceHolder()
    {
        $element = ['fields', 'isLicenceHolder'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementValid($element, 'Y');
    }

    public function testLicenceNumber()
    {
        $element = ['fields', 'licenceNumber'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);

        $this->assertFormElementText($element, 2, 35, ['fields' => ['isLicenceHolder' => 'Y']]);
    }

    public function testOrganisationName()
    {
        $element = ['fields', 'organisationName'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);

        $this->assertFormElementAllowEmpty($element, false, ['fields' => ['isLicenceHolder' => 'N']]);
    }

    public function testBusinessType()
    {
        $element = ['fields', 'businessType'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicRadio($element, false);

        $this->assertFormElementAllowEmpty($element, false, ['fields' => ['isLicenceHolder' => 'N']]);
    }

    public function testTranslateToWelsh()
    {
        $element = ['fields', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
    }

    public function testTermsAgreed()
    {
        $element = ['fields', 'termsAgreed'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementNotValid($element, 'N', \Zend\Validator\Identical::NOT_SAME);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }
}
