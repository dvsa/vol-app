<?php

namespace OlcsTest\Form\Model\Form;

use OlcsTest\TestHelpers\AbstractFormValidationTestCase;

/**
 * Class BusRegApplicationsOperatorFilterFormTest
 *
 * @group FormTests
 */
class BusRegApplicationsOperatorFilterFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegApplicationsOperatorFilterForm::class;

    public function testStatus()
    {
        $element = ['fields', 'status'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUpdateResults()
    {
        $element = ['form-actions', 'updateResults'];
        $this->assertFormElementActionButton($element);
    }
}
