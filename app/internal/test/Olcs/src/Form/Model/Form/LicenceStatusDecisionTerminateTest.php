<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class LicenceStatusDecisionTerminateTest
 *
 * @group FormTests
 */
class LicenceStatusDecisionTerminateTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\LicenceStatusDecisionTerminate::class;

    public function testTerminateDate()
    {
        $element = ['licence-decision', 'terminateDate'];

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

    public function testConfirm()
    {
        $this->assertFormElementActionButton(['form-actions', 'confirm']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
