<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class ComplaintTest
 *
 * @group FormTests
 */
class ComplaintTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Complaint::class;

    public function testComplaintDate()
    {
        $element = ['fields', 'complaintDate'];
        $this->assertFormElementDate($element);
    }

    public function testComplainantForename()
    {
        $element = ['fields', 'complainantForename'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testComplainantFamilyName()
    {
        $element = ['fields', 'complainantFamilyName'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testDriverForename()
    {
        $element = ['fields', 'driverForename'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testDriverFamilyName()
    {
        $element = ['fields', 'driverFamilyName'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testDescription()
    {
        $element = ['fields', 'description'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testVrm()
    {
        $element = ['fields', 'vrm'];

        $this->assertFormElementRequired($element, false);

        $this->assertFormElementValid($element, 'XX59 GTB');
        $this->assertFormElementValid($element, 'FOO1');
        // ToDo: removed as part of VOL-2922 - reinstate or expand test as requirements for VRM validation fully elaborated
        //$this->assertFormElementNotValid($element, 'FOO', 'invalid');
    }

    public function testComplaintType()
    {
        $element = ['fields', 'complaintType'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testClosedDate()
    {
        $element = ['fields', 'closedDate'];
        $this->assertFormElementDate($element);
    }

    public function testStatus()
    {
        $element = ['fields', 'status'];
        $this->assertFormElementDynamicSelect($element, true);
    }

    public function testCase()
    {
        $element = ['fields', 'case'];
        $this->assertFormElementHidden($element);
    }

    public function testId()
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['fields', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
