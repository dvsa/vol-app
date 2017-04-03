<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class LicenceStatusDecisionCurtailTest
 *
 * @group FormTests
 */
class LicenceStatusDecisionCurtailTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\LicenceStatusDecisionCurtail::class;

    public function testImmediateAffect()
    {
        $this->assertFormElementRequired(
            ['licence-decision-affect-immediate', 'immediateAffect'],
            true
        );
    }

    public function testFromDate()
    {
        $element = ['licence-decision', 'curtailFrom'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDateTime($element);
    }

    public function testToDate()
    {
        $element = ['licence-decision', 'curtailTo'];

        $yesterdayDate = new \DateTimeImmutable('yesterday');
        $tomorrowDate = new \DateTimeImmutable('tomorrow');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $yesterdayDate->format('Y'),
                'month'  => $yesterdayDate->format('m'),
                'day'    => $yesterdayDate->format('h'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ],
            [
                'licence-decision' => [
                    'curtailFrom' => [
                        'year'   => $tomorrowDate->format('Y'),
                        'month'  => $tomorrowDate->format('m'),
                        'day'    => $tomorrowDate->format('j'),
                        'hour'   => 12,
                        'minute' => 12,
                        'second' => 12,
                    ],
                ],
            ]
        );
    }

    public function testDecisions()
    {
        $this->assertFormElementDynamicSelect(
            ['licence-decision-legislation', 'decisions']
        );
    }

    public function testAffectImmediate()
    {
        $this->assertFormElementDynamicSelect(
            ['form-actions', 'affectImmediate']
        );
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testRemoveButton()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'remove']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
