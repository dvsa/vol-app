<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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
        $element = ['data', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testDataVersion()
    {
        $element = ['data', 'version'];
        $this->assertFormElementHidden($element);
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
}
