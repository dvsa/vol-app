<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * @group FormTests
 */
class DocumentSendTest extends AbstractFormValidationTestCase
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

    public function testCloseButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'close']);
    }
}
