<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;

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

        $this->assertFormElementNotValid(
            $element,
            'ABC',
            [Digits::NOT_DIGITS]
        );

        $this->assertFormElementNumber(
            $element,
            1,
            null,
            [GreaterThan::NOT_GREATER]
        );
    }

    public function testSubmitAndCancelButtons()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );

        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
