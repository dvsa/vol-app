<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class DisqualifyTest
 *
 * @group FormTests
 */
class DisqualifyTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Disqualify::class;

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testStartDate()
    {
        $element = ['startDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, true);
    }

    public function testPeriod()
    {
        $element = ['period'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementNumber($element);
    }

    public function testNotes()
    {
        $element = ['notes'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 4000);
    }

    public function testNameReadOnly()
    {
        $element = ['name'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testIsDisqualified()
    {
        $element = ['isDisqualified'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
