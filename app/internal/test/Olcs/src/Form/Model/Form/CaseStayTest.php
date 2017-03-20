<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class CaseStayTest
 *
 * @group FormTests
 */
class CaseStayTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\CaseStay::class;

    public function testId()
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['fields', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testCase()
    {
        $element = ['fields', 'case'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
