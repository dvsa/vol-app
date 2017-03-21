<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Select;
use Zend\Validator\GreaterThan;

/**
 * Class CreateFeeTest
 *
 * @group FormTests
 */
class CreateFeeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\CreateFee::class;

    public function testId()
    {
        $this->assertFormElementHidden(['fee-details', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fee-details', 'version']);
    }

    public function testCreatedDate()
    {
        $element = ['fee-details', 'createdDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDate($element);
    }

    public function testFeeType()
    {
        $element = ['fee-details', 'feeType'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Select::class);
    }

    public function testIrfoGvPermit()
    {
        $this->assertFormElementRequired(
            ['fee-details', 'irfoGvPermit'],
            true
        );
    }

    public function testIrfoPsvAuth()
    {
        $this->assertFormElementRequired(
            ['fee-details', 'irfoPsvAuth'],
            true
        );
    }

    public function testAmount()
    {
        $element = ['fee-details', 'amount'];

        $this->assertFormElementValid(
            $element,
            0
        );

        $this->assertFormElementNotValid(
            $element,
            '',
            ['invalid']
        );

        $this->assertFormElementNotValid(
            $element,
            'XXX',
            ['invalid']
        );
    }

    public function testQuantity()
    {
        $element = ['fee-details', 'quantity'];
        $this->assertFormElementNumber(
            $element,
            1,
            null,
            [GreaterThan::NOT_GREATER_INCLUSIVE]
        );
    }

    public function testVatRate()
    {
        $this->assertFormElementRequired(
            ['fee-details', 'vatRate'],
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
