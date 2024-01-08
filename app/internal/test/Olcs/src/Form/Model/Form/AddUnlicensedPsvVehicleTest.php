<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class AddUnlicensedPsvVehicleTest
 *
 * @group FormTests
 */
class AddUnlicensedPsvVehicleTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\AddUnlicensedPsvVehicle::class;

    public function testOrganisation()
    {
        $this->assertFormElementHidden(['organisation']);
    }

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
