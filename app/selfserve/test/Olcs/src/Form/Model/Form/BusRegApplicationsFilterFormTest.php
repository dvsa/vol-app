<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class BusRegApplicationsFilterFormTest
 *
 * @group FormTests
 */
class BusRegApplicationsFilterFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegApplicationsFilterForm::class;

    public function testStatus()
    {
        $element = ['fields', 'status'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUpdateResults()
    {
        $element = ['form-actions', 'updateResults'];
        $this->assertFormElementActionButton($element);
    }
}
