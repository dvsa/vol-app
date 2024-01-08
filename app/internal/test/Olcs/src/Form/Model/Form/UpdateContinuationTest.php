<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class UpdateContinuationTest
 *
 * @group FormTests
 */
class UpdateContinuationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\UpdateContinuation::class;

    public function testReceived()
    {
        $this->assertFormElementRequired(['fields', 'received'], true);
    }

    public function testCheckListStatus()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'checklistStatus'],
            true
        );
    }

    public function testTotalVehicleAuthorisation()
    {
        $this->assertFormElementNumber(
            ['fields', 'totalVehicleAuthorisation'],
            0
        );
    }

    public function testNumberOfDiscs()
    {
        $this->assertFormElementNumber(
            ['fields', 'numberOfDiscs'],
            0
        );
    }

    public function testNumberOfCommunityLicences()
    {
        $this->assertFormElementNumber(
            ['fields', 'numberOfCommunityLicences'],
            0
        );
    }

    public function testMessage()
    {
        $this->assertFormElementRequired(['fields', 'message'], false);
    }

    public function testContinueLicence()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'continueLicence']
        );
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }

    public function testPrintSeperator()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'printSeperator']
        );
    }

    public function testViewContinuation()
    {
        $this->assertFormElementActionLink(['form-actions', 'viewContinuation']);
    }
}
