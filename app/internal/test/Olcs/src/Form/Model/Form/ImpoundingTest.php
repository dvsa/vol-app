<?php

namespace OlcsTest\Form\Model\Form;

use Common\Form\Elements\Validators\DateNotInFuture;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Validator\DateCompare;

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
        $element = ['fields', 'hearingDate'];

        $this->assertFormElementIsRequired($element, false);

        $year = date('Y');
        $month = date('m');
        $day = date('j');
        $hour = date('H');
        $minute = date('i');
        $second = date('s');

        //must be a later date than applicationReceiptDate, and isn't
        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => $year,
                'month' => $month,
                'day'   => $day,
                'hour'  => $hour,
                'minute' => $minute,
                'second'   => $second,
            ],
            [
                DateCompare::NOT_GTE,
            ],
            [
                'fields' => [
                    'applicationReceiptDate' => [
                        'year'  => $year,
                        'month' => $month,
                        'day'   => $day + 1,
                    ],
                    'impoundingType' => 'impt_hearing'
                ],
            ]
        );

        //must be a later date or the same as applicationReceiptDate, and is
        $this->assertFormElementValid(
            $element,
            [
                'year'  => $year,
                'month' => $month,
                'day'   => $day,
                'hour'  => $hour,
                'minute' => $minute,
                'second'   => $second,
            ],
            [
                'fields' => [
                    'applicationReceiptDate' => [
                        'year'  => $year,
                        'month' => $month,
                        'day'   => $day,
                    ],
                    'impoundingType' => 'impt_hearing'
                ],
            ]
        );
    }

    public function testVenue()
    {
        $this->assertFormElementDynamicSelect(['fields', 'venue'], true);
    }

    public function testVenueOther()
    {
        $context = [
            'fields' => [
                'impoundingType' => 'impt_hearing',
                'venue' => 'other'
            ],
        ];

        $this->assertFormElementText(['fields', 'venueOther'], 0, 255, $context);
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
