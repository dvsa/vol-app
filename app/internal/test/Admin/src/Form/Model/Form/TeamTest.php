<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TeamTest
 *
 * @group FormTests
 */
class TeamTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\Team::class;

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testDetailsFormId()
    {
        $this->assertFormElementHidden(
            ['team-details', 'id']
        );
    }

    public function testDetailsFormVersion()
    {
        $this->assertFormElementHidden(
            ['team-details', 'version']
        );
    }

    public function testTableTable()
    {
        $element = ['team-details', 'printerExceptions', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testTableAction()
    {
        $this->assertFormElementNoRender(
            ['team-details', 'printerExceptions', 'action']
        );
    }

    public function testTableRows()
    {
        $this->assertFormElementHidden(
            ['team-details', 'printerExceptions', 'rows']
        );
    }

    public function testTableId()
    {
        $this->assertFormElementNoRender(
            ['team-details', 'printerExceptions', 'id']
        );
    }

    public function testName()
    {
        $element = ['team-details', 'name'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 1, 70);
    }

    public function testDescription()
    {
        $element = ['team-details', 'description'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 1, 255);
    }

    public function testTrafficArea()
    {
        $this->assertFormElementDynamicSelect(
            ['team-details', 'trafficArea'],
            true
        );
    }

    public function testDefaultPrinter()
    {
        $this->assertFormElementDynamicSelect(
            ['team-details', 'defaultPrinter'],
            false
        );
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
