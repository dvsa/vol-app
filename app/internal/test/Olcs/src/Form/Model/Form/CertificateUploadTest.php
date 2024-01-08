<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $element = ['file'];
        $this->assertFormElementMultipleFileUpload($element);
    }
}
