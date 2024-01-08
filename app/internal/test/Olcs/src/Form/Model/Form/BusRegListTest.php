<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class BusRegListTest
 *
 * @group FormTests
 */
class BusRegListTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegList::class;

    public function testStatus()
    {
        $element = ['status'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testFilter()
    {
        $element = ['filter'];
        $this->assertFormElementActionButton($element);
    }

    public function testSort()
    {
        $element = ['sort'];
        $this->assertFormElementHidden($element);
    }

    public function testOrder()
    {
        $element = ['order'];
        $this->assertFormElementHidden($element);
    }

    public function testLimit()
    {
        $element = ['limit'];
        $this->assertFormElementHidden($element);
    }

    public function testPage()
    {
        $element = ['page'];
        $this->assertFormElementHidden($element);
    }
}
