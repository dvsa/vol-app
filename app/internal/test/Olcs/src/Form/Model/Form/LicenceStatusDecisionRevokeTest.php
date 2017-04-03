<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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

    public function testImmediateAffect()
    {
        $this->assertFormElementRequired(
            ['licence-decision-affect-immediate', 'immediateAffect'],
            true
        );
    }

    public function testRevokeFrom()
    {
        $element = ['licence-decision', 'revokeFrom'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDateTime($element);
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
