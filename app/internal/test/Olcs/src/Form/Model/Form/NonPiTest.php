<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class NonPiTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\NonPi::class;

    public function testAgreedByTcDate()
    {
        $this->assertFormElementDate(['fields', 'agreedByTcDate']);
    }

    public function testHearingType()
    {
        $this->assertFormElementDynamicSelect(['fields', 'hearingType'], true);
    }

    public function testHearingDate()
    {
        $element = ['fields', 'hearingDate'];

        $yesterday = new \DateTimeImmutable('+1 day');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $yesterday->format('Y'),
                'month'  => $yesterday->format('m'),
                'day'    => $yesterday->format('j'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ]
        );
    }

    public function testVenue()
    {
        $this->assertFormElementDynamicSelect(['fields', 'venue'], false);
    }

    public function testVenueOther()
    {
        $this->assertFormElementRequired(['fields', 'venueOther'], false);
    }

    public function testWitnessCount()
    {
        $this->assertFormElementRequired(['fields', 'witnessCount'], false);
    }

    public function testPresidingStaffName()
    {
        $this->assertFormElementRequired(
            ['fields', 'presidingStaffName'],
            false
        );
    }

    public function testOutcome()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'outcome'],
            true
        );
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
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
}
