<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\Form\Model\Form\Statement;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Date;
use Common\Validator\Date as CommonDateValidator;

/**
 * Class StatementTest
 *
 * @group FormTests
 */
class StatementTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = Statement::class;

    public function testStatementType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'statementType'],
            true
        );
    }

    public function testAssignedCaseWorker()
    {
        $this->assertFormElementIsRequired(['fields', 'assignedCaseworker'], false);
        $this->assertNull($this->sut->getData()['fields']['assignedCaseworker']);

        $this->assertFormElementDynamicSelect(
            ['fields', 'assignedCaseworker'],
            true
        );
    }

    // ToDo: edited as part of VOL-2922 - reinstate or expand test as requirements for VRM validation fully elaborated
    public function testVrm()
    {
        $this->assertFormElementText(['fields', 'vrm'], true);
    }

    public function testRequestorsForename()
    {
        $element = ['fields', 'requestorsForename'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testRequestorsFamilyName()
    {
        $element = ['fields', 'requestorsFamilyName'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testRequestorsBody()
    {
        $element = ['fields', 'requestorsBody'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 2, 40);
    }

    public function testStoppedDate()
    {
        $element = ['fields', 'stoppedDate'];

        // Invalid date format and no field
        $this->assertFormElementNotValid(
            $element,
            [
                'year' => 'XXX',
                'month' => date('m'),
                'day' => date('j'),
            ],
            [
                CommonDateValidator::DATE_ERR_CONTAINS_STRING,
                CommonDateValidator::DATE_ERR_YEAR_LENGTH,
                Date::INVALID_DATE,
                'invalidField',
            ]
        );

        $this->assertFormElementValid(
            $element,
            [
                'year' => date('Y') - 1,
                'month' => date('m'),
                'day' => date('j'),
            ],
            [
                'fields' => [
                    'requestedDate' => [
                        'year' => date('Y') + 1,
                        'month' => date('m'),
                        'day' => date('j'),
                    ],
                ],
            ]
        );
    }

    public function testRequestedDate()
    {
        $this->assertFormElementDate(['fields', 'requestedDate']);
    }

    public function testIssuedDate()
    {
        $this->assertFormElementDate(['fields', 'issuedDate']);
    }

    public function testContactType()
    {
        $this->assertFormElementDynamicSelect(['fields', 'contactType'], true);
    }

    public function testContactDetailsType()
    {
        $this->assertFormElementHidden(['fields', 'contactDetailsType']);
    }

    public function testContactDetailsId()
    {
        $this->assertFormElementHidden(['fields', 'contactDetailsId']);
    }

    public function testContactDetailsVersion()
    {
        $this->assertFormElementHidden(['fields', 'contactDetailsVersion']);
    }

    public function testPersonId()
    {
        $this->assertFormElementHidden(['fields', 'personId']);
    }

    public function testPersonVersion()
    {
        $this->assertFormElementHidden(['fields', 'personVersion']);
    }

    public function testAuthorisersDecision()
    {
        $element = ['fields', 'authorisersDecision'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    /**
     * This doesn't perform any assertions as per the documentation for
     * assertFormElementPostcodeSearch() in AbstractFormValidationTestCase
     *
     * @doesNotPerformAssertions
     */
    public function testSearchPostcode()
    {
        $this->assertFormElementPostcodeSearch(
            ['requestorsAddress', 'searchPostcode']
        );
    }

    public function testAddressId()
    {
        $this->assertFormElementHidden(['requestorsAddress', 'id']);
    }

    public function testAddressVersion()
    {
        $this->assertFormElementHidden(['requestorsAddress', 'version']);
    }

    public function testAddressLine1()
    {
        $this->assertFormElementIsRequired(
            ['requestorsAddress', 'addressLine1'],
            true
        );
    }

    public function testAddressLine2()
    {
        $this->assertFormElementIsRequired(
            ['requestorsAddress', 'addressLine2'],
            false
        );
    }

    public function testAddressLine3()
    {
        $this->assertFormElementIsRequired(
            ['requestorsAddress', 'addressLine3'],
            false
        );
    }

    public function testAddressLine4()
    {
        $this->assertFormElementIsRequired(
            ['requestorsAddress', 'addressLine4'],
            false
        );
    }

    public function testTown()
    {
        $this->assertFormElementIsRequired(
            ['requestorsAddress', 'town'],
            true
        );
    }

    public function testPostcode()
    {
        $this->assertFormElementIsRequired(
            ['requestorsAddress', 'postcode'],
            true
        );
    }

    public function testCountryCode()
    {
        $this->assertFormElementDynamicSelect(
            ['requestorsAddress', 'countryCode'],
            false
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
}
