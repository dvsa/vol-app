<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Validators\DateNotInFuture;
use Laminas\Validator\Date;
use Common\Validator\Date as DateValidator;

/**
 * Class TransportManagerTest
 *
 * @group FormTests
 */
class TransportManagerTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TransportManager::class;

    public function testTransportManagerDetailsId()
    {
        $this->assertFormElementHidden(['transport-manager-details', 'id']);
    }

    public function testTransportManagerDetailsVersion()
    {
        $this->assertFormElementHidden(
            [
                'transport-manager-details',
                'version',
            ]
        );
    }

    public function testTransportDetailsTransportId()
    {
        $this->assertFormElementHidden(
            ['transport-manager-details', 'transport-manager-id']
        );
    }

    public function testTitle()
    {
        $this->assertFormElementDynamicSelect(
            ['transport-manager-details', 'title'],
            true
        );
    }

    public function testTransportManagerFirstname()
    {
        $this->assertFormElementText(
            ['transport-manager-details', 'firstName'],
            2,
            35
        );
    }

    public function testTransportManagerLastname()
    {
        $this->assertFormElementText(
            ['transport-manager-details', 'lastName'],
            2,
            35
        );
    }

    public function testTransportManagerEmailAddress()
    {
        $this->assertFormElementEmailAddress(
            ['transport-manager-details', 'emailAddress']
        );
    }

    public function testTransportManagerDateOfBirth()
    {
        $element = ['transport-manager-details', 'birthDate'];
        $this->assertFormElementIsRequired($element, true);

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => date('Y') + 1,
                'month' => '1',
                'day'   => '1',
            ],
            [DateNotInFuture::IN_FUTURE]
        );

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => '2017',
                'month' => '10',
                'day'   => 'XXX',
            ],
            [
                DateValidator::DATE_ERR_CONTAINS_STRING,
                Date::INVALID_DATE,
            ]
        );

        $this->assertFormElementValid(
            $element,
            [
                'year'  => '1987',
                'month' => '06',
                'day'   => '15',
            ]
        );
    }

    public function testTransportManagerDetailsType()
    {
        $this->assertFormElementDynamicSelect(
            ['transport-manager-details', 'type'],
            true
        );
    }

    public function testHiddenFields()
    {
        $this->assertFormElementHidden(
            ['transport-manager-details', 'homeCdId']
        );

        $this->assertFormElementHidden(
            ['transport-manager-details', 'homeCdVersion']
        );

        $this->assertFormElementHidden(
            ['transport-manager-details', 'workCdId']
        );

        $this->assertFormElementHidden(
            ['transport-manager-details', 'workCdVersion']
        );

        $this->assertFormElementHidden(
            ['transport-manager-details', 'personId']
        );

        $this->assertFormElementHidden(
            ['transport-manager-details', 'personVersion']
        );

        $this->assertFormElementHidden(
            ['transport-manager-details', 'status']
        );
    }

    public function testTraansportManagerBirthPlace()
    {
        $this->assertFormElementIsRequired(
            ['transport-manager-details', 'birthPlace'],
            true
        );
    }

    /**
     * This doesn't perform any assertions as per the documentation for
     * assertFormElementPostcodeSearch() in AbstractFormValidationTestCase
     *
     * @doesNotPerformAssertions
     */
    public function testHomeSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(
            [
                'home-address',
                'searchPostcode',
            ]
        );
    }

    public function testHomeAddressId()
    {
        $this->assertFormElementHidden(['home-address', 'id']);
    }

    public function testHomeAddressVersion()
    {
        $this->assertFormElementHidden(['home-address', 'version']);
    }

    public function testHomeAddressLine1()
    {
        $this->assertFormElementIsRequired(
            ['home-address', 'addressLine1'],
            false
        );
    }

    public function testHomeAddressLine2()
    {
        $this->assertFormElementIsRequired(
            ['home-address', 'addressLine2'],
            false
        );
    }

    public function testHomeAddressLine3()
    {
        $this->assertFormElementIsRequired(
            ['home-address', 'addressLine3'],
            false
        );
    }

    public function testHomeAddressLine4()
    {
        $this->assertFormElementIsRequired(
            ['home-address', 'addressLine4'],
            false
        );
    }

    public function testHomeTown()
    {
        $this->assertFormElementIsRequired(
            ['home-address', 'town'],
            false
        );
    }

    public function testHomePostcode()
    {
        $this->assertFormElementIsRequired(
            ['home-address', 'postcode'],
            false
        );
    }

    public function testHomeCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['home-address', 'countryCode'],
            false
        );
    }

    /**
    * This doesn't perform any assertions as per the documentation for
    * assertFormElementPostcodeSearch() in AbstractFormValidationTestCase
    *
    * @doesNotPerformAssertions
    */
    public function testWorkSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(
            ['work-address', 'searchPostcode']
        );
    }

    public function testWorkAddressId()
    {
        $this->assertFormElementHidden(['work-address', 'id']);
    }

    public function testWorkAddressVersion()
    {
        $this->assertFormElementHidden(['work-address', 'version']);
    }

    public function testWorkAddressLine1()
    {
        $this->assertFormElementIsRequired(
            ['work-address', 'addressLine1'],
            false
        );
    }

    public function testWorkAddressLine2()
    {
        $this->assertFormElementIsRequired(
            ['work-address', 'addressLine2'],
            false
        );
    }

    public function testWorkAddressLine3()
    {
        $this->assertFormElementIsRequired(
            ['work-address', 'addressLine3'],
            false
        );
    }

    public function testWorkAddressLine4()
    {
        $this->assertFormElementIsRequired(
            ['work-address', 'addressLine4'],
            false
        );
    }

    public function testWorkTown()
    {
        $this->assertFormElementIsRequired(
            ['work-address', 'town'],
            false
        );
    }

    public function testWorkPostcode()
    {
        $this->assertFormElementIsRequired(
            ['work-address', 'postcode'],
            false
        );
    }

    public function testWorkCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['work-address', 'countryCode'],
            false
        );
    }

    public function testSave()
    {
        $this->assertFormElementActionButton(['form-actions', 'save']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
