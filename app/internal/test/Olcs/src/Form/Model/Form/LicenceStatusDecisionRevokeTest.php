<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class LicenceStatusDecisionRevokeTest
 *
 * @group FormTests
 */
class LicenceStatusDecisionRevokeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\LicenceStatusDecisionRevoke::class;

    public function testImmediateAffectRadioButton()
    {
        $element = ['licence-decision-affect-immediate', 'immediateAffect'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testRevokeFrom()
    {
        $this->assertFormElementDateTime(
            ['licence-decision', 'revokeFrom'],
            true
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
