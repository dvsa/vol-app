<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class UploadDocumentTest
 *
 * @group FormTests
 */
class UploadDocumentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\UploadDocument::class;

    public function testCategory()
    {
        $this->assertFormElementDynamicSelect(['details', 'category'], true);
    }

    public function testSubCategory()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'documentSubCategory'],
            true
        );
    }

    public function testDescription()
    {
        $element = ['details', 'description'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 2, 255);
    }

    public function testFile()
    {
        $this->assertFormElementRequired(['details', 'file'], false);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }
}
