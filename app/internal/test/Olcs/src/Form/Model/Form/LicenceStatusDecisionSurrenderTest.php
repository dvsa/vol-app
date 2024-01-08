<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $this->assertFormElementDate(['licence-decision', 'surrenderDate']);
    }

    public function testDecisions()
    {
        $this->assertFormElementDynamicSelect(
            ['licence-decision-legislation', 'decisions'],
            true
        );
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
