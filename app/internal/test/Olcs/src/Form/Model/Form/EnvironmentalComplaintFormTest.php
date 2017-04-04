<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class EnvironmentalComplaintFormTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class EnvironmentalComplaintFormTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\EnvironmentalComplaint::class;

    public function testComplaintDate()
    {
        $this->assertFormElementDate(['fields', 'complaintDate']);
    }

    public function testDescription()
    {
        $element = ['fields', 'description'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testStatus()
    {
        $this->assertFormElementDynamicSelect(['fields', 'status'], true);
    }

    public function testOperatingCentres()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'operatingCentres'],
            true
        );
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

    public function testAddressId()
    {
        $this->assertFormElementHidden(
            ['address', 'id']
        );
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(
            ['address', 'version']
        );
    }

    public function testAddressSearchPostcode()
    {
        $element = ['address', 'searchPostcode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testAddressLine1()
    {
        $element = ['address', 'addressLine1'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine2()
    {
        $element = ['address', 'addressLine2'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine3()
    {
        $element = ['address', 'addressLine3'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 100);
    }

    public function testAddressLine4()
    {
        $element = ['address', 'addressLine4'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testAddressTown()
    {
        $element = ['address', 'town'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 30);
    }

    public function testAddressPostcode()
    {
        $element = ['address', 'postcode'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testAddressCountryCode()
    {
        $element = ['address', 'countryCode'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
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
