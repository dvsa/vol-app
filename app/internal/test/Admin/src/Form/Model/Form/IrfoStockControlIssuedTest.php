<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator\Digits;
use Zend\Validator\GreaterThan;

/**
 * Class IrfoStockControlIssuedTest
 *
 * @group FormTests
 */
class IrfoStockControlIssuedTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\IrfoStockControlIssued::class;

    public function testIrfogvPermitId()
    {
        $element = ['fields', 'irfoGvPermitId'];
        $this->assertFormElementNotValid($element, 'ABC', [ Digits::NOT_DIGITS ]);
        $this->assertFormElementNumber($element, 1, null, [ GreaterThan::NOT_GREATER ]);
    }

    public function testSubmitAndCancelButtons()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);

        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
