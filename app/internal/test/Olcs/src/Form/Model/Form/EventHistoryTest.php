<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class EventHistoryTest
 *
 * @group FormTests
 */
class EventHistoryTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\EventHistory::class;

    public function testEventHistoryDetailsTable()
    {
        $element = ['event-history-details', 'table', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testTableAction()
    {
        $this->assertFormElementNoRender(
            ['event-history-details', 'table', 'action']
        );
    }

    public function testTableRows()
    {
        $this->assertFormElementHidden(
            ['event-history-details', 'table', 'rows']
        );
    }

    public function testTableId()
    {
        $this->assertFormElementNoRender(
            ['event-history-details', 'table', 'id']
        );
    }

    public function testId()
    {
        $this->assertFormElementHidden(
            ['event-history-details', 'id']
        );
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(
            ['event-history-details', 'version']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
