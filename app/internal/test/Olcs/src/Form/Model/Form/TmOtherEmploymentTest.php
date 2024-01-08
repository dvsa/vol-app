<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TmOtherEmploymentTest
 *
 * @group FormTests
 */
class TmOtherEmploymentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TmOtherEmployment::class;

    public function testOtherEmploymentTable()
    {
        $element = ['otherEmployment', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testTableAction()
    {
        $this->assertFormElementNoRender(['otherEmployment', 'action']);
    }

    public function testTableRows()
    {
        $this->assertFormElementHidden(['otherEmployment', 'rows']);
    }

    public function testTableId()
    {
        $this->assertFormElementNoRender(['otherEmployment', 'id']);
    }
}
