<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testDetailsFormId()
    {
        $element = ['team-details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testDetailsFormVersion()
    {
        $element = ['team-details', 'version'];
        $this->assertFormElementHidden($element);
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
        $element = ['team-details', 'printerExceptions', 'action'];
        $this->assertFormElementNoRender($element);
    }

    public function testTableRows()
    {
        $element = ['team-details', 'printerExceptions', 'rows'];
        $this->assertFormElementHidden($element);
    }

    public function testTableId()
    {
        $element = ['team-details', 'printerExceptions', 'id'];
        $this->assertFormElementNoRender($element);
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
        $element = ['team-details', 'trafficArea'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testDefaultPrinter()
    {
        $element = ['team-details', 'defaultPrinter'];
        $this->assertFormElementDynamicSelect($element, false);
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
