<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $this->assertFormElementDate(['licence-decision', 'terminateDate']);
    }

    public function testConfirm()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'confirm']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
