<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class EbsrPackUploadTest
 *
 * @group FormTests
 */
class EbsrPackUploadTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\EbsrPackUpload::class;

    public function testSubmissionType()
    {
        $element = ['fields', 'submissionType'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'ebsrt_new');
        $this->assertFormElementValid($element, 'ebsrt_refresh');
    }

    public function testFilesFile()
    {
        $element = ['fields', 'files', 'file'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testFilesMessages()
    {
        $element = ['fields', 'files', '__messages__'];
        $this->assertFormElementHidden($element);
    }

    public function testFilesUpload()
    {
        $element = ['fields', 'files', 'upload'];
        $this->assertFormElementActionButton($element);
    }

    public function testUploadedFileCount()
    {
        $element = ['fields', 'uploadedFileCount'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }
}
