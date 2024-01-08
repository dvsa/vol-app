<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\Form\Model\Form\Grant;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class GrantTest
 *
 * @group FormTests
 */
class GrantTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Grant::class;

    public function testMessages()
    {
        $element = ['messages', 'message'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementHtml($element);
    }

    public function testInspectionRequestConfirmCreateInspectionReport()
    {
        $element = ['inspection-request-confirm', 'createInspectionRequest'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementValid($element, 'Y');
    }

    public function testInspectionRequestGrantDetailsDueDate()
    {
        $element = ['inspection-request-grant-details', 'dueDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementValid($element, 3);
        $this->assertFormElementValid($element, 6);
        $this->assertFormElementValid($element, 9);
        $this->assertFormElementValid($element, 12);
    }

    public function testCaseWorkerNotes()
    {
        $this->assertFormElementRequired(
            ['inspection-request-grant-details', 'caseworkerNotes'],
            false
        );
    }

    public function testGrantAuthority()
    {
        $element = [Grant::FIELD_GRANT_AUTHORITY];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'X');
    }

    public function testGrant()
    {
        $this->assertFormElementActionButton(['form-actions', 'grant']);
    }

    public function testOverview()
    {
        $this->assertFormElementActionButton(['form-actions', 'overview']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
