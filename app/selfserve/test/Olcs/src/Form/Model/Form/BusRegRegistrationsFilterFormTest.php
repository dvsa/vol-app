<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class BusRegRegistrationsFilterFormTest
 *
 * @group FormTests
 */
class BusRegRegistrationsFilterFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegRegistrationsFilterForm::class;

    public function testOrganisationId()
    {
        $element = ['fields', 'organisationId'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testBusRegStatus()
    {
        $element = ['fields', 'busRegStatus'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testLicId()
    {
        $element = ['fields', 'licId'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testUpdateResults()
    {
        $element = ['form-actions', 'updateResults'];
        $this->assertFormElementActionButton($element);
    }
}
