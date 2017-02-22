<?php

namespace OlcsTest\Form\Model\Form\Lva;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class NewTmUserTest
 *
 * @group FormTests
 */
class NewTmUserTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Lva\NewTmUser::class;

    public function testForename()
    {
        $element = ['data', 'forename'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testFamilyName()
    {
        $element = ['data', 'familyName'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testBirthDate()
    {
        $element = ['data', 'birthDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDate($element);
    }

    public function testHasEmail()
    {
        $element = ['data', 'hasEmail'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementValid($element, 'Y');
    }

    public function testUsername()
    {
        $element = ['data', 'username'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementUsername($element);
    }

    public function testEmailAddress()
    {
        $element = ['data', 'emailAddress'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);

        $this->assertFormElementValid(
            $element,
            'valid@email.com',
            ['data' => ['emailConfirm' => 'valid@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'valid@email.com',
            \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ['data' => ['emailConfirm' => 'other@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'invalid',
            [
                \Dvsa\Olcs\Transfer\Validators\EmailAddress::INVALID_FORMAT,
                \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ],
            ['data' => ['emailConfirm' => 'other@email.com']]
        );
    }

    public function testEmailConfirm()
    {
        $element = ['data', 'emailConfirm'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);
    }

    public function testTranslateToWelsh()
    {
        $element = ['data', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
    }

    public function testEmailGuidance()
    {
        $element = ['data', 'emailGuidance'];
        $this->assertFormElementHtml($element);
    }

    public function testNoEmailGuidance()
    {
        $element = ['data', 'noEmailGuidance'];
        $this->assertFormElementHtml($element);
    }

    public function testContinue()
    {
        $element = ['form-actions', 'continue'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
