<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class AddUnlicensedGoodsVehicleTest
 *
 * @group FormTests
 */
class AddUnlicensedGoodsVehicleTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\AddUnlicensedGoodsVehicle::class;

    public function testDataId()
    {
        $this->assertFormElementHidden(['data', 'id']);
    }

    public function testDataVersion()
    {
        $this->assertFormElementHidden(['data', 'version']);
    }

    public function testDataVrm()
    {
        $element = ['data', 'vrm'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 1, 20);
    }

    public function testDataMakeModel()
    {
        $element = ['data', 'platedWeight'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementNumber($element, 0, 999999);
    }

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'addAnother']
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
