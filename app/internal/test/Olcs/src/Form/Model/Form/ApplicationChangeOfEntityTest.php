<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class ApplicationChangeOfEntityTest
 *
 * @group FormTests
 */
class ApplicationChangeOfEntityTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\ApplicationChangeOfEntity::class;

    public function testOldLicenceNumber()
    {
        $element = ['change-details', 'oldLicenceNo'];
        $this->assertFormElementRequired($element, true);
    }

    public function testRemove()
    {
        $element = ['change-details', 'oldOrganisationName'];
        $this->assertFormElementRequired($element, true);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testRemoveButton()
    {
        $element = ['form-actions', 'remove'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
