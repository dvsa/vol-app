<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class SystemParameterTest
 *
 * @group FormTests
 */
class SystemParameterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\SystemParameter::class;

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testSystemParameterDetailsHiddenId()
    {
        $element = ['system-parameter-details', 'hiddenId'];
        $this->assertFormElementHidden($element);
    }

    public function testSystemParameterDetailsId()
    {
        $element = ['system-parameter-details', 'id'];
        $this->assertFormElementRequired($element, true);
    }

    public function testSystemParameterDetailsParamValue()
    {
        $element = ['system-parameter-details', 'paramValue'];
        $this->assertFormElementRequired($element, true);
    }

    public function testSystemParameterDetailsDescription()
    {
        $element = ['system-parameter-details', 'description'];
        $this->assertFormElementRequired($element, false);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }

    public function testAddAnotherButton()
    {
        $element = ['form-actions', 'addAnother'];
        $this->assertFormElementActionButton($element);
    }
}
