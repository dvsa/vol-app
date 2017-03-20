<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator\Regex;

/**
 * Class TaskAlphaSplitTest
 *
 * @group FormTests
 */
class TaskAlphaSplitTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\TaskAlphaSplit::class;

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementActionButton($element);
    }

    public function testTaskAlphaSplitVersion()
    {
        $element = ['taskAlphaSplit', 'version'];
        $this->assertFormElementActionButton($element);
    }

    public function testTaskAlphaSplitId()
    {
        $element = ['taskAlphaSplit', 'id'];
        $this->assertFormElementActionButton($element);
    }

    public function testTaskAlphaUser()
    {
        $element = ['taskAlphaSplit', 'user'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testTaskAlphaLetters()
    {
        $element = ['taskAlphaSplit', 'letters'];

        $this->assertFormElementRequired($element, true);

        $this->assertFormElementNotValid(
            $element,
            '1234',
            [ Regex::NOT_MATCH ]
        );
        $this->assertFormElementNotValid(
            $element,
            'ABC!!!£££',
            [ Regex::NOT_MATCH ]
        );

        $this->assertFormElementValid($element, 'abcdefgh');
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }

    public function testAddAnother()
    {
        $element = ['form-actions', 'addAnother'];
        $this->assertFormElementActionButton($element);
    }
}
