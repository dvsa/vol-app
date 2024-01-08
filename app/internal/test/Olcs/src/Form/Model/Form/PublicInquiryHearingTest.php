<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;
use Laminas\Validator;

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
        $element = ['fields', 'venueOther'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testHearingDate()
    {
        $element = ['fields', 'hearingDate'];

        $tomorrow = new \DateTimeImmutable('+1 day');

        $this->assertFormElementIsRequired($element, true);
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
        $this->assertFormElementIsRequired($element, true);
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
        $this->assertFormElementIsRequired(['fields', 'witnesses'], true);
    }

    public function testDrivers()
    {
        $element = ['fields', 'drivers'];

        $this->assertFormElementValid($element, 0);
        $this->assertFormElementValid($element, 1);

        $this->assertFormElementValid($element, 99);
        $this->assertFormElementNotValid($element, 100, Validator\Between::NOT_BETWEEN);

        $this->assertFormElementNotValid($element, 'X', Validator\Digits::NOT_DIGITS);
    }

    public function testIsCancelled()
    {
        $this->assertFormElementIsRequired(['fields', 'isCancelled'], true);
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
            ]
        );
    }

    public function testCancelledReason()
    {
        $element = ['fields', 'cancelledReason'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, true);
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
        $this->assertFormElementIsRequired(['fields', 'isAdjourned'], true);
    }

    public function testAdjournedReason()
    {
        $element = ['fields', 'adjournedReason'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testDefinition()
    {
        $this->assertFormElementDynamicSelect(['fields', 'definition'], true);
    }

    public function testDetails()
    {
        $element = ['fields', 'details'];
        $this->assertFormElementIsRequired($element, false);
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
        $this->assertFormElementIsRequired(['fields', 'pubType'], true);
    }

    public function testTrafficAreas()
    {
        $this->assertFormElementIsRequired(['fields', 'trafficAreas'], true);
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
