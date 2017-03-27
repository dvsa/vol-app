<?php

namespace OlcsTest\Form\Model\Form;

use Common\Form\Elements\Validators\DateNotInFuture;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class ImpoundingTest
 *
 * @group FormTests
 */
class ImpoundingTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Impounding::class;

    public function testId()
    {
        $this->assertFormElementHidden(['base', 'id']);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['base', 'case']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['base', 'version']);
    }

    public function testImpoundingType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'impoundingType'],
            true
        );
    }

    public function testApplicationReceiptDate()
    {
        $element = ['fields', 'applicationReceiptDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementNotValid(
            $element,
            ['year' => date('Y') + 1, 'month' => 10, 'day' => 10],
            [DateNotInFuture::IN_FUTURE]
        );
    }

    public function testVrm()
    {
        $element = ['fields', 'vrm'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 1, 20);
    }

    public function testImpoundingLegislationTypes()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'impoundingLegislationTypes'],
            true
        );
    }

    public function testHearingDate()
    {
        $this->assertFormElementRequired(['fields', 'hearingDate'], false);
    }

    public function testVenue()
    {
        $this->assertFormElementDynamicSelect(['fields', 'venue'], true);
    }

    public function testVenueOther()
    {
        $this->assertFormElementDynamicSelect(['fields', 'venueOther'], false);
    }

    public function testPresidingTc()
    {
        $this->assertFormElementDynamicSelect(['fields', 'presidingTc'], false);
    }

    public function testOutcome()
    {
        $this->assertFormElementDynamicSelect(['fields', 'outcome'], false);
    }

    public function testNotes()
    {
        $element = ['fields', 'notes'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testOutcomeSentDate()
    {
        $element = ['fields', 'outcomeSentDate'];
        $this->assertFormElementDate($element);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testPublish()
    {
        $this->assertFormElementActionButton(['form-actions', 'publish']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
