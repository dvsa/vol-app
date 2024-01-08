<?php

namespace OlcsTest\Form\Model\Form\Lva;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class ApplicationUndertakingsTest
 *
 * @group FormTests
 */
class ApplicationUndertakingsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Lva\ApplicationUndertakings::class;

    public function testReview()
    {
        $this->assertFormElementHtml(['declarations', 'review']);
    }

    public function testSummaryDownload()
    {
        $this->assertFormElementHtml(['declarations', 'summaryDownload']);
    }

    public function testHeading()
    {
        $this->assertFormElementHtml(['declarations', 'heading']);
    }

    public function testVerifySignatureText()
    {
        $this->assertFormElementHtml(['declarations', 'verifySignatureText']);
    }

    public function testDeclarationConfirmation()
    {
        $this->assertFormElementRequired(
            ['declarations', 'declarationConfirmation'],
            true
        );
    }

    public function testSaveAndContinue()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'saveAndContinue']
        );
    }

    public function testSave()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'save']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }
}
