<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator\InArray;

/**
 * Class CreateVariationTest
 *
 * @group FormTests
 */
class CreateVariationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\CreateVariation::class;

    public function testMessages()
    {
        $element = ['messages', 'message'];
        $this->assertFormElementHtml($element);
    }

    public function testReceivedDate()
    {
        $element = ['data', 'receivedDate'];
        $this->assertFormElementDate($element);
    }

    public function testFeeRequired()
    {
        $element = ['data', 'feeRequired'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testAppliedVia()
    {
        $element = ['data', 'appliedVia'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementValid($element, 'applied_via_post');
        $this->assertFormElementValid($element, 'applied_via_phone');
        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
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
