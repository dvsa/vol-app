<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Validator\Date as CommonDateValidation;
use Laminas\Validator\Date as LaminasDateValidation;

/**
 * Class EnvironmentalComplaintTest
 *
 * @group FormTests
 */
class EnvironmentalComplaintTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\EnvironmentalComplaint::class;

    public function testComplaintDate()
    {
        $date = new \DateTimeImmutable('now');

        $this->assertFormElementNotValid(
            ['fields', 'complaintDate'],
            [
                'year'  => 'XXXX',
                'month' => $date->format('m'),
                'day'   => $date->format('j'),
            ],
            [
                CommonDateValidation::DATE_ERR_CONTAINS_STRING,
                CommonDateValidation::DATE_ERR_YEAR_LENGTH,
                LaminasDateValidation::INVALID_DATE,
            ]
        );

        $this->assertFormElementValid(
            ['fields', 'complaintDate'],
            [
                'year'  => $date->format('Y'),
                'month' => $date->format('m'),
                'day'   => $date->format('j'),
            ]
        );
    }

    public function testComplainantForename()
    {
        $element = ['fields', 'complainantForename'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testComplainantFamilyName()
    {
        $element = ['fields', 'complainantFamilyName'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testDescription()
    {
        $element = ['fields', 'description'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testStatus()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'status'],
            true
        );
    }

    public function testOperatingCentres()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'operatingCentres'],
            true
        );
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
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementPostcodeSearch($element);
    }

    public function testAddressLine1()
    {
        $element = ['address', 'addressLine1'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine2()
    {
        $element = ['address', 'addressLine2'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 90);
    }

    public function testAddressLine3()
    {
        $element = ['address', 'addressLine3'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 100);
    }

    public function testAddressLine4()
    {
        $element = ['address', 'addressLine4'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 0, 35);
    }

    public function testAddressTown()
    {
        $element = ['address', 'town'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 30);
    }

    public function testAddressPostcode()
    {
        $element = ['address', 'postcode'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testAddressCountryCode()
    {
        $element = ['address', 'countryCode'];
        $this->assertFormElementIsRequired($element, true);
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
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
