<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator\Digits;

/**
 * Class FeePaymentTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class FeePaymentTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\FeePayment::class;

    public function testBackToFee()
    {
        $this->assertFormElementHidden(['details', 'backToFee']);
    }

    public function testMaxAmount()
    {
        $this->assertFormElementHtml(['details', 'maxAmount']);
    }

    public function testMinAmountForValidator()
    {
        $this->assertFormElementHidden(['details', 'minAmountForValidator']);
    }

    public function testMaxAmountForValidator()
    {
        $this->assertFormElementHidden(['details', 'maxAmountForValidator']);
    }

    public function testPaymentType()
    {
        $this->assertFormElementDynamicSelect(['details', 'paymentType'], true);
    }

    public function testReceived()
    {
        $this->assertFormElementRequired(['details', 'received'], true);
    }

    public function testReceiptDate()
    {
        $this->assertFormElementDate(['details', 'receiptDate']);
    }

    public function testPayer()
    {
        $this->assertFormElementRequired(['details', 'payer'], true);
    }

    public function testSlipNo()
    {
        $this->assertFormElementRequired(
            ['details', 'slipNo'],
            true,
            [Digits::INVALID]
        );
    }

    public function testChequeNo()
    {
        $this->assertFormElementRequired(['details', 'chequeNo'], false);
    }

    public function testChequeDate()
    {
        $element = ['details', 'chequeDate'];

        $dateNow = new \DateTimeImmutable('now');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year' => $dateNow->format('Y'),
                'month' => $dateNow->format('m'),
                'day' => $dateNow->format('j')
            ]
        );

        $this->assertFormElementRequired($element, false);
    }

    public function testPoNo()
    {
        $this->assertFormElementAllowEmpty(['details', 'poNo'], true);
    }

    public function testCustomerReference()
    {
        $this->assertFormElementRequired(['details', 'customerReference'], true);
    }

    public function testCustomerName()
    {
        $this->assertFormElementRequired(['details', 'customerName'], true);
    }

    public function testAddressId()
    {
        $this->assertFormElementHidden(
            ['address', 'id']
        );
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(
            ['address', 'version']
        );
    }

    public function testAddressSearchPostcode()
    {
        $element = ['address', 'searchPostcode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testAddressLine1()
    {
        $element = ['address', 'addressLine1'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine2()
    {
        $element = ['address', 'addressLine2'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine3()
    {
        $element = ['address', 'addressLine3'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 100);
    }

    public function testAddressLine4()
    {
        $element = ['address', 'addressLine4'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testAddressTown()
    {
        $element = ['address', 'town'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 30);
    }

    public function testAddressPostcode()
    {
        $element = ['address', 'postcode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testAddressCountryCode()
    {
        $element = ['address', 'countryCode'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testPay()
    {
        $this->assertFormElementActionButton(['form-actions', 'pay']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
