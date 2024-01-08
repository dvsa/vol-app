<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class TmEmploymentTest
 *
 * @group FormTests
 */
class TmEmploymentTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TmEmployment::class;

    public function testEmployerName()
    {
        $element = ['tm-employer-name-details', 'employerName'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, null, 90);
    }

    /**
     * This doesn't perform any assertions as per the documentation for
     * assertFormElementPostcodeSearch() in AbstractFormValidationTestCase
     *
     * @doesNotPerformAssertions
     */
    public function testSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(['address', 'searchPostcode']);
    }

    public function testAddressId()
    {
        $this->assertFormElementHidden(['address', 'id']);
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(['address', 'version']);
    }

    public function testAddressLine1()
    {
        $this->assertFormElementRequired(['address', 'addressLine1'], false);
    }

    public function testAddressLine2()
    {
        $this->assertFormElementRequired(['address', 'addressLine2'], false);
    }

    public function testAddressLine3()
    {
        $this->assertFormElementRequired(['address', 'addressLine3'], false);
    }

    public function testAddressLine4()
    {
        $this->assertFormElementRequired(['address', 'addressLine4'], false);
    }

    public function testTown()
    {
        $this->assertFormElementRequired(['address', 'town'], false);
    }

    public function testPostcode()
    {
        $this->assertFormElementRequired(['address', 'postcode'], false);
    }

    public function testCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['address', 'countryCode'],
            false
        );
    }

    public function testEmploymentDetailsId()
    {
        $this->assertFormElementHidden(['tm-employment-details', 'id']);
    }

    public function testEmploymentDetailsVersion()
    {
        $this->assertFormElementHidden(['tm-employment-details', 'version']);
    }

    public function testEmploymentDetailsPosition()
    {
        $element = ['tm-employment-details', 'position'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, null, 45);
    }

    public function testEmploymentDetailsHoursPerWeek()
    {
        $element = ['tm-employment-details', 'hoursPerWeek'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, null, 100);
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
