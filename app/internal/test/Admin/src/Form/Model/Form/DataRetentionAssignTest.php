<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class DataRetentionAssignTest
 *
 * @group FormTests
 */
class DataRetentionAssignTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\DataRetentionAssign::class;

    public function testAssignedTo()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'assignedTo'],
            true
        );
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
