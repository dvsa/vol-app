<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class Schedule41TransferTest
 *
 * @group FormTests
 */
class Schedule41TransferTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Schedule41Transfer::class;

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

    public function testSurrenderLicence()
    {
        $this->assertFormElementRequired(['surrenderLicence'], true);
    }

    public function testTransfer()
    {
        $this->assertFormElementActionButton(['transfer']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['cancel']);
    }
}
