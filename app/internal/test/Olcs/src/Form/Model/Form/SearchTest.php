<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class SearchTest
 *
 * @group FormTests
 */
class SearchTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Search::class;

    public function testLicenceNumber()
    {
        $this->assertFormElementRequired(['search', 'licNo'], false);
    }

    public function testOperatorName()
    {
        $this->assertFormElementRequired(['search', 'operatorName'], false);
    }

    public function testPostcode()
    {
        $this->assertFormElementRequired(['search', 'postcode'], false);
    }

    public function testForename()
    {
        $this->assertFormElementRequired(['search', 'forename'], false);
    }

    public function testFamilyName()
    {
        $this->assertFormElementRequired(['search', 'familyName'], false);
    }

    public function testBirthDate()
    {
        $this->assertFormElementRequired(['search', 'birthDate'], false);
    }

    public function testSearchButton()
    {
        $this->assertFormElementActionButton(['search', 'search']);
    }

    public function testAddress()
    {
        $this->assertFormElementRequired(['search-advanced', 'address'], false);
    }

    public function testTown()
    {
        $this->assertFormElementRequired(['search-advanced', 'town'], false);
    }

    public function testCaseNumber()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'caseNumber'],
            false
        );
    }

    public function testTransportManagerId()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'transportManagerId'],
            false
        );
    }

    public function testOperatorId()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'operatorId'],
            false
        );
    }

    public function testVehicleRegMark()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'vehicleRegMark'],
            false
        );
    }

    public function testDiskSerialNumber()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'diskSerialNumber'],
            false
        );
    }

    public function testFabsRef()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'fabsRef'],
            false
        );
    }

    public function testCompanyNo()
    {
        $this->assertFormElementRequired(
            ['search-advanced', 'companyNo'],
            false
        );
    }

    public function testAdvancedSearchButton()
    {
        $this->assertFormElementActionButton(['advanced']);
    }
}
