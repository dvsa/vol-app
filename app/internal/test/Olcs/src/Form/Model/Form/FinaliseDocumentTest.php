<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class FinaliseDocumentTest
 *
 * @group FormTests
 */
class FinaliseDocumentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\FinaliseDocument::class;

    public function testCategoryText()
    {
        $this->assertFormElementText(
            ['category'],
            0,
            null
        );
    }

    public function testSubCategoryText()
    {
        $this->assertFormElementText(
            ['subCategory'],
            0,
            null
        );
    }

    public function testTemplate()
    {
        $this->assertFormElementHtml(['template']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancelFinalise()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancelFinalise']
        );
    }

    public function testBack()
    {
        $this->assertFormElementActionButton(['form-actions', 'back']);
    }
}
