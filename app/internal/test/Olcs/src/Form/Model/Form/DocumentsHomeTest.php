<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;

/**
 * Class DocumentsHomeTest
 *
 * @group FormTests
 */
class DocumentsHomeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\DocumentsHome::class;

    public function testFilterButton()
    {
        $this->assertFormElementActionButton(['filter']);
    }

    public function testCategory()
    {
        $this->assertFormElementDynamicSelect(['category']);
    }

    public function testSubCategory()
    {
        $this->assertFormElementDynamicSelect(['documentSubCategory']);
    }

    public function testIsExternal()
    {
        $this->assertFormElementDynamicSelect(['isExternal']);
    }

    public function testFormat()
    {
        $element = ['format'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testShowDocs()
    {
        $element = ['showDocs'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }
}
