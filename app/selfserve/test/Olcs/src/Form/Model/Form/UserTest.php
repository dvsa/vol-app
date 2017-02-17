<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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
    protected $formName = \Olcs\Form\Model\Form\User::class;

    public function testLoginId()
    {
        $element = ['main', 'loginId'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementUsername($element);
    }

    public function testForename()
    {
        $element = ['main', 'forename'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testFamilyName()
    {
        $element = ['main', 'familyName'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testEmailAddress()
    {
        $element = ['main', 'emailAddress'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);

        $this->assertFormElementValid(
            $element,
            'valid@email.com',
            ['main' => ['emailConfirm' => 'valid@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'valid@email.com',
            \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ['main' => ['emailConfirm' => 'other@email.com']]
        );

        $this->assertFormElementNotValid(
            $element,
            'invalid',
            [
                \Dvsa\Olcs\Transfer\Validators\EmailAddress::INVALID_FORMAT,
                \Common\Form\Elements\Validators\EmailConfirm::NOT_SAME,
            ],
            ['main' => ['emailConfirm' => 'other@email.com']]
        );
    }

    public function testEmailConfirm()
    {
        $element = ['main', 'emailConfirm'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element);
    }

    public function testPermission()
    {
        $element = ['main', 'permission'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'user');
        $this->assertFormElementValid($element, 'admin');
        $this->assertFormElementValid($element, 'tm');
    }

    public function testTranslateToWelsh()
    {
        $element = ['main', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
    }

    public function testCurrentPermission()
    {
        $element = ['main', 'currentPermission'];
        $this->assertFormElementHidden($element);
    }

    public function testId()
    {
        $element = ['main', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['main', 'version'];
        $this->assertFormElementHidden($element);
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
