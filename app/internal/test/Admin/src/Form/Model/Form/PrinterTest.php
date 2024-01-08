<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class PrinterTest
 *
 * @group FormTests
 */
class PrinterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\Printer::class;

    public function testPrinterId()
    {
        $element = ['printer-details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testPrinterVersion()
    {
        $element = ['printer-details', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testPrinterName()
    {
        $element = ['printer-details', 'printerName'];
        $this->assertFormElementText($element, 1, 45);
    }

    public function testPrinterDescription()
    {
        $element = ['printer-details', 'description'];
        $this->assertFormElementText($element, 1, 255);
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testAddAnotherButton()
    {
        $element = ['form-actions', 'addAnother'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
