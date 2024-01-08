<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class TaskTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\Task::class;

    public function testLink()
    {
        $this->assertFormElementHtml(['details', 'link']);
    }

    public function testStatus()
    {
        $this->assertFormElementHtml(['details', 'status']);
    }

    public function testCancelledDate()
    {
        $element = ['details', 'actionDate'];

        $tomorrow = new \DateTimeImmutable('+1 day');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $tomorrow->format('Y'),
                'month'  => $tomorrow->format('m'),
                'day'    => $tomorrow->format('j'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ]
        );
    }

    public function testUrgent()
    {
        $this->assertFormElementIsRequired(['details', 'urgent'], true);
    }

    public function testCategory()
    {
        $this->assertFormElementDynamicSelect(['details', 'category'], true);
    }

    public function testTaskSubCategory()
    {
        $this->assertFormElementDynamicSelect(['details', 'subCategory'], true);
    }

    public function testDescription()
    {
        $this->assertFormElementText(['details', 'description'], 2, 255);
    }

    public function testAssignedToTeam()
    {
        $element = ['assignment', 'assignedToTeam'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testAssignedToUser()
    {
        $element = ['assignment', 'assignedToUser'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testTableTable()
    {
        $element = ['taskHistory', 'table'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testTableAction()
    {
        $this->assertFormElementNoRender(['taskHistory', 'action']);
    }

    public function testTableRows()
    {
        $this->assertFormElementHidden(['taskHistory', 'rows']);
    }

    public function testTableId()
    {
        $this->assertFormElementNoRender(['taskHistory', 'id']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testLinkType()
    {
        $this->assertFormElementHidden(['linkType']);
    }

    public function testLinkId()
    {
        $this->assertFormElementHidden(['linkId']);
    }
}
