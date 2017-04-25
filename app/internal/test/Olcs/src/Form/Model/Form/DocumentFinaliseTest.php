<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * @group FormTests
 */
class DocumentFinaliseTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\DocumentSend::class;

    public function testEmailButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'email']);
    }

    public function testPostAndPrintButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'printAndPost']);
    }

    public function testCancelButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
