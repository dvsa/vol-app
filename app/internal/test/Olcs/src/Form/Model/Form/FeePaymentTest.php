<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Validators\DateNotInFuture;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Digits;
use Laminas\Validator\Date;

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
        $this->assertFormElementIsRequired(['details', 'received'], true);
    }

    public function testReceiptDate()
    {
        $element = ['details', 'receiptDate'];

        $now = new \DateTimeImmutable('now');
        $date = $now->format('Y') . '-' . $now->format('m') . '-' . $now->format('d');

        $this->assertFormElementValid($element, $date);

        $this->assertFormElementAllowEmpty(
            $element,
            false
        );

        $this->assertFormElementIsRequired($element, true);
    }

    public function testPayer()
    {
        $this->assertFormElementIsRequired(['details', 'payer'], true);
    }

    public function testSlipNo()
    {
        $this->assertFormElementIsRequired(
            ['details', 'slipNo'],
            true,
            [NotEmpty::IS_EMPTY, Digits::INVALID]
        );
    }

    public function testChequeNo()
    {
        $this->assertFormElementAllowEmpty(['details', 'chequeNo'], true);
    }

    public function testChequeDate()
    {
        $element = ['details', 'chequeDate'];

        $previousYear = new \DateTime('-1 year');
        $date = $previousYear->format('Y') . '-' . $previousYear->format('m') . '-' . $previousYear->format('d');

        $this->assertFormElementValid($element, $date);
        $this->assertFormElementAllowEmpty($element, true);

        $nextYear = new \DateTime('+1 year');
        $date = $nextYear->format('Y') . '-' . $nextYear->format('m') . '-' . $nextYear->format('d');

        $this->assertFormElementNotValid(
            $element,
            $date,
            [ DateNotInFuture::IN_FUTURE ],
            ['details' => ['paymentType' => 'fpm_cheque']]
        );

        $this->assertFormElementNotValid(
            $element,
            null,
            [ NotEmpty::IS_EMPTY ],
            ['details' => ['paymentType' => 'fpm_cheque']]
        );
    }

    public function testPoNo()
    {
        $this->assertFormElementIsRequired(['details', 'poNo'], true);
    }

    public function testCustomerReference()
    {
        $this->assertFormElementIsRequired(
            ['details', 'customerReference'],
            true
        );
    }

    public function testCustomerName()
    {
        $this->assertFormElementIsRequired(['details', 'customerName'], true);
    }

    public function testAddressId()
    {
        $this->assertFormElementHidden(['address', 'id']);
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(['address', 'version']);
    }

    public function testAddressSearchPostcode()
    {
        $element = ['address', 'searchPostcode'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testAddressLine1()
    {
        $element = ['address', 'addressLine1'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine2()
    {
        $element = ['address', 'addressLine2'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine3()
    {
        $element = ['address', 'addressLine3'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 100);
    }

    public function testAddressLine4()
    {
        $element = ['address', 'addressLine4'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testAddressTown()
    {
        $element = ['address', 'town'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 30);
    }

    public function testAddressPostcode()
    {
        $element = ['address', 'postcode'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testAddressCountryCode()
    {
        $element = ['address', 'countryCode'];
        $this->assertFormElementIsRequired($element, true);
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
