<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class MessageTest
 *
 * @group FormTests
 */
class MessageTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Message::class;

    public function testMessage()
    {
        $element = ['messages', 'message'];
        $this->assertFormElementHtml($element);
    }

    public function testOk()
    {
        $element = ['form-actions', 'ok'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
