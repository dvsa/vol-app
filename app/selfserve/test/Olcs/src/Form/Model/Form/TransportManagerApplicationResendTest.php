<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class TransportManagerApplicationResendTest
 *
 * @group FormTests
 */
class TransportManagerApplicationResendTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TransportManagerApplicationResend::class;

    public function testEmailAddress()
    {
        $element = ['emailAddress'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);
    }

    public function testSubmit()
    {
        $element = ['submit'];
        $this->assertFormElementActionButton($element);
    }
}
