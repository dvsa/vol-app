<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class WithdrawTest
 *
 * @group FormTests
 */
class WithdrawTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Withdraw::class;

    public function testReason()
    {
        $this->assertFormElementRequired(['withdraw-details', 'reason'], true);
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
