<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class RefundFeeTest
 *
 * @group FormTests
 */
class RefundFeeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\RefundFee::class;

    public function testMessage()
    {
        $this->assertFormElementHtml(['messages', 'message']);
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

    /**
     * This doesn't perform any assertions as per the documentation for
     * assertFormElementPostcodeSearch() in AbstractFormValidationTestCase
     *
     * @doesNotPerformAssertions
     */
    public function testSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(['address', 'searchPostcode']);
    }

    public function testAddressId()
    {
        $this->assertFormElementHidden(['address', 'id']);
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(['address', 'version']);
    }

    public function testAddressLine1()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine1'],
            true
        );
    }

    public function testAddressLine2()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine2'],
            false
        );
    }

    public function testAddressLine3()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine3'],
            false
        );
    }

    public function testAddressLine4()
    {
        $this->assertFormElementIsRequired(
            ['address', 'addressLine4'],
            false
        );
    }

    public function testTown()
    {
        $this->assertFormElementIsRequired(
            ['address', 'town'],
            true
        );
    }

    public function testPostcode()
    {
        $this->assertFormElementIsRequired(
            ['address', 'postcode'],
            true
        );
    }

    public function testCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['address', 'countryCode'],
            false
        );
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
