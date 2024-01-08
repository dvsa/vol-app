<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

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

    public function testImmediateAffectRadioButton()
    {
        $element = ['licence-decision-affect-immediate', 'immediateAffect'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testSuspendFrom()
    {
        $this->assertFormElementDateTime(
            ['licence-decision', 'suspendFrom'],
            true
        );
    }

    public function testSuspendTo()
    {
        $element = ['licence-decision', 'suspendTo'];
        $this->assertFormElementDateTimeNotValidCheck($element);
        $this->assertFormElementDateTimeValidCheck(
            $element,
            null,
            [
                'licence-decision' => [
                    'suspendFrom' => [
                        'year'    => date('y') + 1,
                        'month'   => '10',
                        'day'     => '01',
                        'hour'    => '21',
                        'minute'  => '30',
                        'seconds' => '10',
                    ],
                ],
            ]
        );
    }

    public function testDecisions()
    {
        $this->assertFormElementDynamicSelect(
            ['licence-decision-legislation', 'decisions'],
            true
        );
    }

    public function testAffectImmediate()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'affectImmediate']
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

    public function testRemove()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'remove']
        );
    }
}
