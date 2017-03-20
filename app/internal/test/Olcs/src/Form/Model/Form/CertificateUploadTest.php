<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Types\AttachFilesButton;

/**
 * Class CertificateUploadTest
 *
 * @group FormTests
 */
class CertificateUploadTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\CertificateUpload::class;

    public function testFileUpload()
    {
        $element = ['file','file'];
        $this->assertFormElementType($element, AttachFilesButton::class);
        $this->assertFormElementRequired($element, false);
    }

    public function testMessage()
    {
        $element = ['file', '__messages__'];
        $this->assertFormElementHidden($element);
    }

    public function testUploadButton()
    {
        $element = ['file', 'upload'];
        $this->assertFormElementActionButton($element);
    }
}
