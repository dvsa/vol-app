<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Radio;

/**
 * Class PublicInquiryHearingTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class PublicInquiryHearingTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\PublicInquiryHearing::class;

    public function testVenue()
    {
        $this->assertFormElementDynamicSelect(['fields', 'venue'], false);
    }

    public function testVenueOther()
    {
        $this->assertFormElementRequired(['fields', 'venueOther'], false);
    }

    public function testHearingDate()
    {
        $element = ['fields', 'hearingDate'];

        $tomorrow = new \DateTimeImmutable('+1 day');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $tomorrow->format('Y'),
                'month'  => $tomorrow->format('m'),
                'day'    => $tomorrow->format('j'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ]
        );
    }

    public function testIsFullDay()
    {
        $element = ['fields', 'isFullDay'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testPresidingTc()
    {
        $this->assertFormElementDynamicSelect(['fields', 'presidingTc'], true);
    }

    public function testPresidedByRole()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'presidedByRole'],
            true
        );
    }

    public function testWitnesses()
    {
        $this->assertFormElementRequired(['fields', 'witnesses'], true);
    }

    public function testIsCancelled()
    {
        $this->assertFormElementRequired(['fields', 'isCancelled'], true);
    }

    public function testCancelledDate()
    {
        $element = ['fields', 'cancelledDate'];

        $tomorrow = new \DateTimeImmutable('+1 day');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $tomorrow->format('Y'),
                'month'  => $tomorrow->format('m'),
                'day'    => $tomorrow->format('j'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ]
        );
    }

    public function testCancelledReason()
    {
        $this->assertFormElementRequired(['fields', 'cancelledReason'], false);
    }

    public function testAdjournedDate()
    {
        $element = ['fields', 'adjournedDate'];

        $tomorrow = new \DateTimeImmutable('+1 day');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $tomorrow->format('Y'),
                'month'  => $tomorrow->format('m'),
                'day'    => $tomorrow->format('j'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ]
        );
    }

    public function testIsAdjourned()
    {
        $this->assertFormElementRequired(['fields', 'isAdjourned'], true);
    }

    public function testAdjournedReason()
    {
        $this->assertFormElementRequired(['fields', 'adjournedReason'], false);
    }

    public function testDefinition()
    {
        $this->assertFormElementDynamicSelect(['fields', 'definition'], true);
    }

    public function testDetails()
    {
        $element = ['fields', 'details'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testPubType()
    {
        $this->assertFormElementRequired(['fields', 'pubType'], true);
    }

    public function testTrafficAreas()
    {
        $this->assertFormElementRequired(['fields', 'trafficAreas'], true);
    }

    public function testPi()
    {
        $this->assertFormElementHidden(['fields', 'pi']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testPublish()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'publish']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
