<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class InterimTest
 *
 * @group FormTests
 */
class InterimTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Interim::class;

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testInterimRequest()
    {
        $element = ['requested', 'interimRequested'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testInterimReason()
    {
        $this->assertFormElementRequired(['data', 'interimReason'], false);
    }

    public function testInterimStart()
    {
        $this->assertFormElementDate(['data', 'interimStart']);
    }

    public function testInterimEnd()
    {
        $this->assertFormElementDate(['data', 'interimEnd']);
    }

    public function testInterimAuthHgvVehicles()
    {
        $this->assertFormElementRequired(
            ['data', 'interimAuthHgvVehicles'],
            false
        );
    }

    public function testInterimAuthLgvVehicles()
    {
        $this->assertFormElementRequired(
            ['data', 'interimAuthLgvVehicles'],
            false
        );
    }

    public function testInterimAuthTrailers()
    {
        $this->assertFormElementRequired(
            ['data', 'interimAuthTrailers'],
            false
        );
    }

    public function testOperatingCentresTableTable()
    {
        $element = ['operatingCentres', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testOperatingCentresTableAction()
    {
        $this->assertFormElementNoRender(['operatingCentres', 'action']);
    }

    public function testOperatingCentresTableRows()
    {
        $this->assertFormElementHidden(['operatingCentres', 'rows']);
    }

    public function testOperatingCentresTableId()
    {
        $this->assertFormElementNoRender(['operatingCentres', 'id']);
    }

    public function testVehiclesTableTable()
    {
        $element = ['vehicles', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testVehiclesTableAction()
    {
        $this->assertFormElementNoRender(['vehicles', 'action']);
    }

    public function testVehiclesTableRows()
    {
        $this->assertFormElementHidden(['vehicles', 'rows']);
    }

    public function testVehicleTableId()
    {
        $this->assertFormElementNoRender(['vehicles', 'id']);
    }

    public function testInterimStatus()
    {
        $this->assertFormElementDynamicSelect(
            ['interimStatus', 'status'],
            true
        );
    }

    public function testSave()
    {
        $this->assertFormElementActionButton(['form-actions', 'save']);
    }

    public function testGrant()
    {
        $this->assertFormElementActionButton(['form-actions', 'grant']);
    }

    public function testRefuse()
    {
        $this->assertFormElementActionButton(['form-actions', 'refuse']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    public function testReprint()
    {
        $this->assertFormElementActionButton(['form-actions', 'reprint']);
    }
}
