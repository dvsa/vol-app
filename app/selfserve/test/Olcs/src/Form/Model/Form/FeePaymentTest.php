<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class FeePaymentTest
 *
 * @group FormTests
 */
class FeePaymentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\FeePayment::class;

    public function testAmount()
    {
        $element = ['amount'];
        $this->assertFormElementHtml($element);
    }

    public function testCard()
    {
        $element = ['storedCards', 'card'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);
    }

    public function testPay()
    {
        $element = ['form-actions', 'pay'];
        $this->assertFormElementActionButton($element);
    }

    public function testCustomCancel()
    {
        $element = ['form-actions', 'customCancel'];
        $this->assertFormElementActionButton($element);
    }
}
