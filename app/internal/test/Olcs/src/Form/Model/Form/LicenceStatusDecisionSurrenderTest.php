<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class LicenceStatusDecisionSurrenderTest
 *
 * @group FormTests
 */
class LicenceStatusDecisionSurrenderTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\LicenceStatusDecisionSurrender::class;

    public function testSurrenderDate()
    {
        $element = ['licence-decision', 'surrenderDate'];

        $yesterday = new \DateTimeImmutable('-1 day');

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDateTime(
            $element,
            true,
            [
                'year'  => $yesterday->format('Y'),
                'month' => $yesterday->format('m'),
                'day'   => $yesterday->format('j'),
            ]
        );
    }

    public function testDecisions()
    {
        $this->assertFormElementDynamicSelect(
            ['licence-decision-legislation', 'decisions']
        );
    }

    public function testConfirm()
    {
        $this->assertFormElementActionButton(['form-actions', 'confirm']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
