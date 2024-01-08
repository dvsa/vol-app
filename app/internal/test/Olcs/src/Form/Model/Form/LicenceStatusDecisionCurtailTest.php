<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

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

    public function testImmediateAffectRadioButton()
    {
        $element = ['licence-decision-affect-immediate', 'immediateAffect'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testCurtailFrom()
    {
        $this->assertFormElementDateTime(
            ['licence-decision', 'curtailFrom'],
            true
        );
    }

    public function testCurtailTo()
    {
        $element = ['licence-decision', 'curtailTo'];
        $this->assertFormElementDateTimeNotValidCheck($element);
        $this->assertFormElementDateTimeValidCheck(
            $element,
            null,
            [
                'licence-decision' => [
                    'curtailFrom' => [
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
