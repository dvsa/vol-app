<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class QualificationTest
 *
 * @group FormTests
 */
class QualificationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Qualification::class;

    public function testQualificationDetailsId()
    {
        $this->assertFormElementHidden(['qualification-details', 'id']);
    }

    public function testQualificationDetailsVersion()
    {
        $this->assertFormElementHidden(['qualification-details', 'version']);
    }

    public function testQualificationType()
    {
        $this->assertFormElementDynamicSelect(
            ['qualification-details', 'qualificationType'],
            true
        );
    }

    public function testSerialNo()
    {
        $element = ['qualification-details', 'serialNo'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 50);
    }

    public function testIssuedDate()
    {
        $this->assertFormElementDate(['qualification-details', 'issuedDate']);
    }

    public function testCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['qualification-details', 'countryCode'],
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

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(['form-actions', 'addAnother']);
    }
}
