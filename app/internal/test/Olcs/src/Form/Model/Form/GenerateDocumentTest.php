<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;

/**
 * Class GenerateDocumentTest
 *
 * @group FormTests
 */
class GenerateDocumentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\GenerateDocument::class;

    public function testCategorySelect()
    {
        $this->assertFormElementDynamicSelect(['details', 'category'], true);
    }

    public function testSubCategorySelect()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'documentSubCategory'],
            true
        );
    }

    public function testDocumentTemplate()
    {
        $element = ['details', 'documentTemplate'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
