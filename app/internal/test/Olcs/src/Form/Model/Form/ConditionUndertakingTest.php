<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Laminas\Form\Element\Select;
use Laminas\Validator\NotEmpty;

/**
 * Class ConditionUndertakingTest
 *
 * @group FormTests
 */
class ConditionUndertakingTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\ConditionUndertaking::class;

    public function testNotes()
    {
        $element = ['fields', 'notes'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 5, 8000);
    }

    public function testFulfilled()
    {
        $element = ['fields', 'fulfilled'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testAttachedTo()
    {
        $element = ['fields', 'attachedTo'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Select::class);
    }

    public function testType()
    {
        $element = ['fields', 'type'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testIsDraft()
    {
        $element = ['fields', 'isDraft'];
        $this->assertFormElementHidden($element);
    }

    public function testCase()
    {
        $element = ['fields', 'case'];
        $this->assertFormElementHidden($element);
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

    public function testFieldsConditionCategory()
    {
        $element = ['fields', 'conditionCategory'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementIsRequired($element, true);
    }
}
