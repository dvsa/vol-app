<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class IrfoStockControlFilterTest
 *
 * @group FormTests
 */
class IrfoStockControlFilterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\IrfoStockControlFilter::class;

    public function testIrfoCountry()
    {
        $element = ['irfoCountry'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testValidForYear()
    {
        $element = ['validForYear'];
        $this->assertFormElementValid($element, date('Y') - 20);

        $errorMessages = [
            'notInArray'
        ];

        $this->assertFormElementNotValid(
            $element,
            date('Y') - 101,
            $errorMessages
        );
    }

    public function testStatus()
    {
        $this->assertFormElementDynamicSelect(['status'], true);
    }

    public function testFilterButton()
    {
        $this->assertFormElementActionButton(['filter']);
    }
}
