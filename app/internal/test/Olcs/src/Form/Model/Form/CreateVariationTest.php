<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\InArray;

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
        $this->assertFormElementHtml(['messages', 'message']);
    }

    public function testReceivedDate()
    {
        $this->assertFormElementDate(['data', 'receivedDate']);
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
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
