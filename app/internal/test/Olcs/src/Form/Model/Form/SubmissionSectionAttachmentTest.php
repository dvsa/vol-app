<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $element = ['attachments'];
        $this->assertFormElementMultipleFileUpload($element);
    }

    public function testSectionId()
    {
        $this->assertFormElementHidden(['sectionId']);
    }
}
