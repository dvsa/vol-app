<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;

/**
 * Class TasksHomeTest
 *
 * @group FormTests
 */
class TasksHomeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TasksHome::class;

    public function testAssignedToTeam()
    {
        $element = ['assignedToTeam'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testAssignedToUser()
    {
        $element = ['assignedToUser'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testCategory()
    {
        $this->assertFormElementDynamicSelect(['category'], true);
    }

    public function testTaskSubCategory()
    {
        $this->assertFormElementDynamicSelect(['taskSubCategory'], true);
    }

    public function testDate()
    {
        $this->assertFormElementDynamicSelect(['date']);
    }

    public function testStatus()
    {
        $this->assertFormElementDynamicSelect(['status']);
    }

    public function testShowTasks()
    {
        $element = ['showTasks'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testUrgent()
    {
        $this->assertFormElementRequired(['urgent'], true);
    }

    public function testFilter()
    {
        $this->assertFormElementActionButton(['filter']);
    }

    public function testSort()
    {
        $this->assertFormElementHidden(['sort']);
    }

    public function testOrder()
    {
        $this->assertFormElementHidden(['order']);
    }

    public function testLimit()
    {
        $this->assertFormElementHidden(['limit']);
    }
}
