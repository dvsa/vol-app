<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class LicenceStatusDecisionSuspendTest
 *
 * @group FormTests
 */
class LicenceStatusDecisionSuspendTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\LicenceStatusDecisionSuspend::class;

    public function testImmediateAffect()
    {
        $this->assertFormElementRequired(
            ['licence-decision-affect-immediate', 'immediateAffect'],
            true
        );
    }

    public function testSuspendFrom()
    {
        $element = ['licence-decision', 'suspendFrom'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDateTime($element);
    }

    public function testSuspendTo()
    {
        $element = ['licence-decision', 'suspendTo'];

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
                    'suspendFrom' => [
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
        $this->assertFormElementActionButton([
            'form-actions',
            'affectImmediate',
        ]);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    public function testRemove()
    {
        $this->assertFormElementActionButton(['form-actions', 'remove']);
    }
}
