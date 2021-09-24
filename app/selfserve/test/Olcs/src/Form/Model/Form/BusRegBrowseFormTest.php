<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class BusRegBrowseFormTest
 *
 * @group FormTests
 */
class BusRegBrowseFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegBrowseForm::class;

    public function testTrafficAreas()
    {
        $element = ['fields', 'trafficAreas'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicMultiCheckbox($element);
    }

    public function testStatus()
    {
        $element = ['fields', 'status'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testAcceptedDate()
    {
        $element = ['fields', 'acceptedDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDate($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testExport()
    {
        $element = ['form-actions', 'export'];
        $this->assertFormElementActionButton($element);
    }
}
