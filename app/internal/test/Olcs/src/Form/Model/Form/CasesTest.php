<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class CasesTest
 *
 * @group FormTests
 */
class CasesTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Cases::class;

    public function testCaseType()
    {
        $element = ['fields', 'caseType'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testCategorys()
    {
        $element = ['fields', 'categorys'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testDescription()
    {
        $element = ['fields', 'description'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 1024);
    }

    public function testEcmsNo()
    {
        $element = ['fields', 'ecmsNo'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 2, 45);
    }

    public function testOutcomes()
    {
        $element = ['fields', 'outcomes'];
        $this->assertFormElementDynamicSelect($element, true);
    }

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
