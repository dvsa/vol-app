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
        $element = ['fee-details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['fee-details', 'version'];
        $this->assertFormElementHidden($element);
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
        $element = ['fee-details', 'irfoGvPermit'];
        $this->assertFormElementRequired($element, true);
    }

    public function testIrfoPsvAuth()
    {
        $element = ['fee-details', 'irfoPsvAuth'];
        $this->assertFormElementRequired($element, true);
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
        $element = ['fee-details', 'vatRate'];
        $this->assertFormElementRequired($element, false);
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
