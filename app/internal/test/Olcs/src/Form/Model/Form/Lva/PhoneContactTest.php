<?php

namespace OlcsTest\Form\Model\Form\Lva;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class PhoneContactTest
 *
 * @group FormTests
 */
class PhoneContactTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Lva\PhoneContact::class;

    public function testPhoneContactType()
    {
        $this->assertFormElementDynamicRadio(
            ['details', 'phoneContactType'],
            true
        );
    }

    public function testPhoneNumber()
    {
        $this->assertFormElementPhone(['details', 'phoneNumber']);
    }

    public function testContactDetailsId()
    {
        $this->assertFormElementHidden(['details', 'contactDetailsId']);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['details', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['details', 'version']);
    }

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'addAnother']
        );
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
