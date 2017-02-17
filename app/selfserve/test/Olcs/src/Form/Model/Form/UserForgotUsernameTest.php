<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class UserForgotUsernameTest
 *
 * @group FormTests
 */
class UserForgotUsernameTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\UserForgotUsername::class;

    public function testLicenceNumber()
    {
        $element = ['fields', 'licenceNumber'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 2, 18);
    }

    public function testEmailAddress()
    {
        $element = ['fields', 'emailAddress'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementEmailAddress($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }
}
