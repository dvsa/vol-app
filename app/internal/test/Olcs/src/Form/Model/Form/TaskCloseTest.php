<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TaskCloseTest
 *
 * @group FormTests
 */
class TaskCloseTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TaskClose::class;

    public function testLabel()
    {
        $this->assertFormElementHtml(['details', 'label']);
    }

    public function testClose()
    {
        $this->assertFormElementActionButton(['form-actions', 'close']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
