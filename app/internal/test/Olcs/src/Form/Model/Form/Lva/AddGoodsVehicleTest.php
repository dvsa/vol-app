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
        $this->assertFormElementHidden(['data', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['data', 'version']);
    }

    public function testVrm()
    {
        $this->assertFormElementVrm(['data', 'vrm']);
    }

    public function testPlatedWeight()
    {
        $this->assertFormElementVehiclePlatedWeight(['data', 'platedWeight']);
    }

    public function testLicenceVehicleId()
    {
        $this->assertFormElementHidden(['licence-vehicle', 'id']);
    }

    public function testLicenceVehicleVersion()
    {
        $this->assertFormElementHidden(['licence-vehicle', 'version']);
    }

    public function testLicenceVehicleReceivedDate()
    {
        $element = ['licence-vehicle', 'receivedDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testLicenceVehicleSpecifiedDate()
    {
        $element = ['licence-vehicle', 'specifiedDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testLicenceVehicleRemovalDate()
    {
        $element = ['licence-vehicle', 'removalDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testDiscNumber()
    {
        $element = ['licence-vehicle', 'discNo'];
        $this->assertFormElementText($element);
        $this->assertFormElementRequired($element, false);
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
