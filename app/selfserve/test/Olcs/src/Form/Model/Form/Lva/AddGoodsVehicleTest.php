<?php

namespace OlcsTest\Form\Model\Form\Lva;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class AddGoodsVehicleTest
 *
 * @group FormTests
 */
class AddGoodsVehicleTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Lva\AddGoodsVehicle::class;

    public function testId()
    {
        $element = ['data', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['data', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testVrm()
    {
        $element = ['data', 'vrm'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementVrm($element);
    }

    public function testPlatedWeight()
    {
        $element = ['data', 'platedWeight'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementVehiclePlatedWeight($element);
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

    public function testAddAnother()
    {
        $element = ['form-actions', 'addAnother'];
        $this->assertFormElementActionButton($element);
    }
}
