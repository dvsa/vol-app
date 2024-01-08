<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\InputFilters\Checkbox;
use Laminas\Form\Element\Select;

/**
 * Class CompaniesHouseAlertFiltersTest
 *
 * @group FormTests
 */
class CompaniesHouseAlertFiltersTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\CompaniesHouseAlertFilters::class;

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

    public function testIncludeClosed()
    {
        $element = ['includeClosed'];
        $this->assertFormElementType($element, Checkbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testTypeOfChange()
    {
        $element = ['typeOfChange'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }
}
