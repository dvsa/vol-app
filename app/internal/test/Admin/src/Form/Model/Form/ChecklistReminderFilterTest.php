<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class ChecklistReminderFilterTest
 *
 * @group FormTests
 */
class ChecklistReminderFilterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\ChecklistReminderFilter::class;

    public function testFilterDate()
    {
        $element = ['filters','date'];
        $this->assertFormElementMonthSelect($element);
    }

    public function testFilterButton()
    {
        $element = ['filter'];
        $this->assertFormElementActionButton($element);
    }
}
