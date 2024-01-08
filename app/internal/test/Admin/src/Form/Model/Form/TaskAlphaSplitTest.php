<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Regex;

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
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testId()
    {
        $this->assertFormElementActionButton(['id']);
    }

    public function testTaskAlphaSplitVersion()
    {
        $this->assertFormElementActionButton(
            ['taskAlphaSplit', 'version']
        );
    }

    public function testTaskAlphaSplitId()
    {
        $this->assertFormElementActionButton(
            ['taskAlphaSplit', 'id']
        );
    }

    public function testTaskAlphaUser()
    {
        $this->assertFormElementDynamicSelect(
            ['taskAlphaSplit', 'user'],
            true
        );
    }

    public function testTaskAlphaLetters()
    {
        $element = ['taskAlphaSplit', 'letters'];

        $this->assertFormElementRequired($element, true);

        $this->assertFormElementNotValid(
            $element,
            '1234',
            [Regex::NOT_MATCH]
        );
        $this->assertFormElementNotValid(
            $element,
            'ABC!!!£££',
            [Regex::NOT_MATCH]
        );

        $this->assertFormElementValid($element, 'abcdefgh');
    }

    public function testVersion()
    {
        $this->assertFormElementActionButton(['version']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'addAnother']
        );
    }
}
