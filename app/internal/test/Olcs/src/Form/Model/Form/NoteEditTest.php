<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class NoteEditTest
 *
 * @group FormTests
 */
class NoteEditTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\NoteEdit::class;

    public function testComment()
    {
        $element = ['fields', 'comment'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testPriority()
    {
        $element = ['fields', 'priority'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
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
}
