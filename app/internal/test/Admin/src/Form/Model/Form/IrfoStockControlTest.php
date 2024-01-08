<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Between;

/**
 * Class IrfoStockControlTest
 *
 * @group FormTests
 */
class IrfoStockControlTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\IrfoStockControl::class;

    public function testIrfoCountry()
    {
        $element = ['fields', 'irfoCountry'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testValidForYear()
    {
        $element = ['fields', 'validForYear'];
        $this->assertFormElementValid($element, date('Y') - 20);

        $errorMessages = [
            'notInArray'
        ];

        $this->assertFormElementNotValid($element, date('Y') - 101, $errorMessages);
    }

    public function testStatus()
    {
        $element = ['fields', 'status'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testSerialNoStartAndEnd()
    {
        $element = ['fields', 'serialNoStart'];
        $this->assertFormElementValid($element, 2, ['serialNoEnd' => 100]);

        $element = ['fields', 'serialNoEnd'];
        $this->assertFormElementValid($element, 100, ['serialNoStart' => 2]);
    }

    public function testSubmitAndCancelButtons()
    {
        $element = ['form-actions','submit'];
        $this->assertFormElementActionButton($element);

        $element = ['form-actions','cancel'];
        $this->assertFormElementActionButton($element);
    }
}
