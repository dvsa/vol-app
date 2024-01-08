<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $this->assertFormElementRequired(
            ['change-details', 'oldLicenceNo'],
            true
        );
    }

    public function testRemove()
    {
        $this->assertFormElementRequired(
            ['change-details', 'oldOrganisationName'],
            true
        );
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testRemoveButton()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'remove']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
