<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TransportManagerMergeTest
 *
 * @group FormTests
 */
class TransportManagerMergeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TransportManagerMerge::class;

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    // read only field
    public function testFromTmName()
    {
        $this->assertFormElementRequired(['fromTmName'], false);
    }

    public function testTmId()
    {
        $this->assertFormElementRequired(['toTmId'], true);
    }

    public function testConfirm()
    {
        $this->assertFormElementRequired(['confirm'], true);
    }
}
