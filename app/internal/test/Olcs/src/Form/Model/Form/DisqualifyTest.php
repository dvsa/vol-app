<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
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
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
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
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
