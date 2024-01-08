<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class GracePeriodsTest
 *
 * @group FormTests
 */
class GracePeriodsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\GracePeriods::class;

    public function testTableTable()
    {
        $element = ['table', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testTableAction()
    {
        $this->assertFormElementNoRender(['table', 'action']);
    }

    public function testTableRows()
    {
        $this->assertFormElementHidden(['table', 'rows']);
    }

    public function testTableId()
    {
        $this->assertFormElementNoRender(['table', 'id']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(['form-actions', 'addAnother']);
    }
}
