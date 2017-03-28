<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Types\AttachFilesButton;

/**
 * Class SubmissionSectionAttachmentTest
 *
 * @group FormTests
 */
class SubmissionSectionAttachmentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SubmissionSectionAttachment::class;

    public function testFileUpload()
    {
        $element = ['attachments','file'];
        $this->assertFormElementType($element, AttachFilesButton::class);
        $this->assertFormElementRequired($element, false);
    }

    public function testMessage()
    {
        $this->assertFormElementHidden(['attachments', '__messages__']);
    }

    public function testUploadButton()
    {
        $this->assertFormElementActionButton(['attachments', 'upload']);
    }

    public function testSectionId()
    {
        $this->assertFormElementHidden(['sectionId']);
    }
}
