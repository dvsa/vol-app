<?php

/**
 * Companies House Compare Business Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CliTest\BusinessService\Service;

use Common\BusinessService\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;
use Cli\BusinessService\Service\CompaniesHouseCompare;
use Common\Service\Entity\CompaniesHouseAlertEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Exception\ResourceNotFoundException;

/**
 *  Companies House Compare Business Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseCompareTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new CompaniesHouseCompare();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test process method
     *
     * @dataProvider noChangesProvider
     */
    public function testProcessNoChanges($companyNumber, $stubResponse, $stubSavedData)
    {
        // mocks
        $mockApi = m::mock();
        $this->sm->setService('CompaniesHouseApi', $mockApi);

        $mockEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseCompany', $mockEntityService);

        // expectations
        $mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubResponse);

        $mockEntityService
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubSavedData);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_NO_OP, $result->getType());
    }

    /**
     * Test process method
     *
     * @dataProvider changesProvider
     *
     * @param string $companyNumber
     * @param array $stubResponse api response
     * @param array $stubSavedData last saved company data
     * @param array $orgData organisation data
     * @param array $expectedAlertData
     * @param array $expectedSaveData new company data to save
     */
    public function testProcessChanges(
        $companyNumber,
        $stubResponse,
        $stubSavedData,
        $orgData,
        $expectedAlertData,
        $expectedSaveData = []
    ) {
        $alertSaveResult = ['ALERT'];
        $companySaveResult = ['id' => 99];

        // mocks
        $mockApi = m::mock();
        $this->sm->setService('CompaniesHouseApi', $mockApi);

        $mockCompanyEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseCompany', $mockCompanyEntityService);

        $mockAlertEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseAlert', $mockAlertEntityService);

        $mockOrganisationEntityService = m::mock();
        $this->sm->setService('Entity\Organisation', $mockOrganisationEntityService);

        // expectations
        $mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubResponse);

        $mockCompanyEntityService
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubSavedData)
            ->shouldReceive('saveNew')
            ->once()
            ->with($expectedSaveData)
            ->andReturn($companySaveResult);

        $mockOrganisationEntityService
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with($companyNumber)
            ->once()
            ->andReturn($orgData);

        $mockAlertEntityService
            ->shouldReceive('saveNew')
            ->with($expectedAlertData)
            ->once()
            ->andReturn($alertSaveResult);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_SUCCESS, $result->getType());
        $this->assertEquals("Alert created", $result->getMessage());
        $this->assertEquals(
            [
                'company' => $companySaveResult,
                'alert' => $alertSaveResult,
            ],
            $result->getData()
        );
    }

    /**
     * Test process method when company not found
     */
    public function testProcessCompanyNotFound()
    {
        // data
        $companyNumber = '01234567';

        $expectedAlertData = array(
            'companyOrLlpNo' => $companyNumber,
            'name' => 'NEW COMPANY',
            'organisation' => 69,
            'reasons' => array(
                array(
                    'reasonType' => CompaniesHouseAlertEntityService::REASON_INVALID_COMPANY_NUMBER,
                ),
            ),
        );

        $orgData = array(
            'Count' => 1,
            'Results' => array(
                array(
                    'id' => 69,
                    'name' => 'NEW COMPANY',
                ),
            ),
        );

        $saveResult = ['ALERT'];

        // mocks
        $mockApi = m::mock();
        $this->sm->setService('CompaniesHouseApi', $mockApi);

        $mockAlertEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseAlert', $mockAlertEntityService);

        $mockOrganisationEntityService = m::mock();
        $this->sm->setService('Entity\Organisation', $mockOrganisationEntityService);

        // expectations
        $mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber)
            ->andReturn(false);

        $mockAlertEntityService
            ->shouldReceive('saveNew')
            ->with($expectedAlertData)
            ->once()
            ->andReturn($saveResult);

        $mockOrganisationEntityService
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with($companyNumber)
            ->once()
            ->andReturn($orgData);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_SUCCESS, $result->getType());
        $this->assertEquals("Alert created", $result->getMessage());
        $this->assertEquals($saveResult, $result->getData());
    }

    /**
     * Test process exception handling
     */
    public function testProcessException()
    {
        // data
        $companyNumber = '01234567';

        // mocks
        $mockApi = m::mock();
        $this->sm->setService('CompaniesHouseApi', $mockApi);

        // expectations
        $mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber)
            ->andThrow(new \Exception('oops!'));

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_FAILED, $result->getType());
        $this->assertEquals("oops!", $result->getMessage());
    }

    /**
     * Test process method when company was not previously stored
     *
     * @dataProvider firstTimeProvider
     */
    public function testProcessCompanyFirstTimeFound($companyNumber, $stubResponse, $expectedSaveData)
    {
        $saveResult = ['id' => 99];

        // mocks
        $mockApi = m::mock();
        $this->sm->setService('CompaniesHouseApi', $mockApi);

        $mockCompanyEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseCompany', $mockCompanyEntityService);

        // expectations
        $mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubResponse);

        $mockCompanyEntityService
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn(false)
            ->shouldReceive('saveNew')
            ->with($expectedSaveData)
            ->once()
            ->andReturn($saveResult);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_SUCCESS, $result->getType());
        $this->assertEquals("Saved new company id 99", $result->getMessage());
        $this->assertEquals($saveResult, $result->getData());
    }

    /**
     * Test process method when company was not previously stored
     *
     * @dataProvider orgNotFoundProvider
     */
    public function testProcessCompanyOrganisationNotFound($companyNumber, $stubResponse, $stubSavedData)
    {
        // mocks
        $mockApi = m::mock();
        $this->sm->setService('CompaniesHouseApi', $mockApi);

        $mockCompanyEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseCompany', $mockCompanyEntityService);

        $mockAlertEntityService = m::mock();
        $this->sm->setService('Entity\CompaniesHouseAlert', $mockAlertEntityService);

        $mockOrganisationEntityService = m::mock();
        $this->sm->setService('Entity\Organisation', $mockOrganisationEntityService);

        // expectations
        $mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubResponse);

        $mockCompanyEntityService
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn($stubSavedData);

        $mockOrganisationEntityService
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with($companyNumber)
            ->once()
            ->andReturn(false);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_FAILED, $result->getType());
        $this->assertEquals("No organisation found for company no. '03127414'", $result->getMessage());
    }

    public function noChangesProvider()
    {
        return array(
            'no changes' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'last_full_members_list_date' => '2014-11-17',
                    'accounts' => array(
                        'next_due' => '2015-09-30',
                        'last_accounts' => array(
                            'type' => 'full',
                            'made_up_to' => '2013-12-31',
                        ),
                        'accounting_reference_date' => array(
                            'day' => '31',
                            'month' => '12',
                        ),
                        'next_made_up_to' => '2014-12-31',
                        'overdue' => false,
                    ),
                    'date_of_creation' => '1995-11-17',
                    'sic_codes' => array(
                        0 => '62020',
                    ),
                    'undeliverable_registered_office_address' => false,
                    'annual_return' => array(
                        'next_due' => '2015-12-15',
                        'overdue' => false,
                        'next_made_up_to' => '2015-11-17',
                        'last_made_up_to' => '2014-11-17',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'jurisdiction' => 'england-wales',
                    'company_number' => '03127414',
                    'type' => 'ltd',
                    'has_been_liquidated' => false,
                    'has_insolvency_history' => false,
                    'etag' => 'ec52ec76d16210d1133df1b4c9bb8f797a38d09c',
                    'officer_summary' => array(
                        'resigned_count' => 17,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => '1979-02-16',
                                'appointed_on' => '2008-09-15',
                            ),
                            1 => array(
                                'appointed_on' => '2008-09-15',
                                'officer_role' => 'director',
                                'name' => 'HALL, Philip',
                                'date_of_birth' => '1968-12-16',
                            ),
                            2 => array(
                                'appointed_on' => '2011-11-14',
                                'officer_role' => 'director',
                                'name' => 'SKINNER, Mark James',
                                'date_of_birth' => '1969-06-13',
                            ),
                        ),
                        'active_count' => 3,
                    ),
                    'company_status' => 'active',
                    'can_file' => true,
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(
                        array(
                            'dateOfBirth' => '1979-02-16',
                            'name' => 'DILLON, Andrew',
                            'role' => 'director'
                        ),
                        array(
                            'dateOfBirth' => '1968-12-16',
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ),
                        array (
                            'dateOfBirth' => '1969-06-13',
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ),
                    ),
                    'companyStatus' => 'active',
                    'country' => null,
                )
            ),
        );
    }

    /**
     * @return array
     */
    public function changesProvider()
    {
        return array(
            'status change only' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'last_full_members_list_date' => '2014-11-17',
                    'accounts' => array(
                        'next_due' => '2015-09-30',
                        'last_accounts' => array(
                            'type' => 'full',
                            'made_up_to' => '2013-12-31',
                        ),
                        'accounting_reference_date' => array(
                            'day' => '31',
                            'month' => '12',
                        ),
                        'next_made_up_to' => '2014-12-31',
                        'overdue' => false,
                    ),
                    'date_of_creation' => '1995-11-17',
                    'sic_codes' => array(
                        0 => '62020',
                    ),
                    'undeliverable_registered_office_address' => false,
                    'annual_return' => array(
                        'next_due' => '2015-12-15',
                        'overdue' => false,
                        'next_made_up_to' => '2015-11-17',
                        'last_made_up_to' => '2014-11-17',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'jurisdiction' => 'england-wales',
                    'company_number' => '03127414',
                    'type' => 'ltd',
                    'has_been_liquidated' => false,
                    'has_insolvency_history' => false,
                    'etag' => 'ec52ec76d16210d1133df1b4c9bb8f797a38d09c',
                    'officer_summary' => array(
                        'resigned_count' => 17,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => '1979-02-16',
                                'appointed_on' => '2008-09-15',
                            ),
                            1 => array(
                                'appointed_on' => '2008-09-15',
                                'officer_role' => 'director',
                                'name' => 'HALL, Philip',
                                'date_of_birth' => '1968-12-16',
                            ),
                            2 => array(
                                'appointed_on' => '2011-11-14',
                                'officer_role' => 'director',
                                'name' => 'SKINNER, Mark James',
                                'date_of_birth' => '1969-06-13',
                            ),
                        ),
                        'active_count' => 3,
                    ),
                    'company_status' => 'liquidation',
                    'can_file' => true,
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(
                        array(
                            'dateOfBirth' => '1979-02-16',
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ),
                        array(
                            'dateOfBirth' => '1968-12-16',
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ),
                        array (
                            'dateOfBirth' => '1969-06-13',
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ),
                    ),
                    'companyStatus' => 'active',
                    'country' => null,
                ),
                'orgData' => array(
                    'Count' => 1,
                    'Results' => array(
                        array(
                            'id' => 69,
                            'name' => 'Valtech',
                        ),
                    ),
                ),
                'expectedAlertData' => array(
                    'companyOrLlpNo' => '03127414',
                    'name' => 'Valtech',
                    'organisation' => 69,
                    'reasons' => array(
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_STATUS_CHANGE,
                        ),
                    ),
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'liquidation',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => array(
                        array(
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                            'dateOfBirth' => '1979-02-16',
                        ),
                        array(
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                            'dateOfBirth' => '1968-12-16',
                        ),
                        array (
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                            'dateOfBirth' => '1969-06-13',
                        ),
                    ),
                ),
            ),
            'name status address and people change' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '122 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'last_full_members_list_date' => '2014-11-17',
                    'accounts' => array(
                        'next_due' => '2015-09-30',
                        'last_accounts' => array(
                            'type' => 'full',
                            'made_up_to' => '2013-12-31',
                        ),
                        'accounting_reference_date' => array(
                            'day' => '31',
                            'month' => '12',
                        ),
                        'next_made_up_to' => '2014-12-31',
                        'overdue' => false,
                    ),
                    'date_of_creation' => '1995-11-17',
                    'sic_codes' => array(
                        0 => '62020',
                    ),
                    'undeliverable_registered_office_address' => false,
                    'annual_return' => array(
                        'next_due' => '2015-12-15',
                        'overdue' => false,
                        'next_made_up_to' => '2015-11-17',
                        'last_made_up_to' => '2014-11-17',
                    ),
                    'company_name' => 'VALTECH 2 LIMITED',
                    'jurisdiction' => 'england-wales',
                    'company_number' => '03127414',
                    'type' => 'ltd',
                    'has_been_liquidated' => false,
                    'has_insolvency_history' => false,
                    'etag' => 'ec52ec76d16210d1133df1b4c9bb8f797a38d09c',
                    'officer_summary' => array(
                        'resigned_count' => 18,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => '1979-02-16',
                                'appointed_on' => '2008-09-15',
                            ),
                            1 => array(
                                'appointed_on' => '2011-11-14',
                                'officer_role' => 'director',
                                'name' => 'SMITH, John',
                                'date_of_birth' => '1969-06-13',
                            ),
                        ),
                        'active_count' => 2,
                    ),
                    'company_status' => 'dissolved',
                    'can_file' => true,
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(
                        array(
                            'dateOfBirth' => '1979-02-16',
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ),
                        array(
                            'dateOfBirth' => '1968-12-16',
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ),
                        array (
                            'dateOfBirth' => '1969-06-13',
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ),
                    ),
                    'companyStatus' => 'active',
                    'country' => null,
                ),
                'orgData' => array(
                    'Count' => 1,
                    'Results' => array(
                        array(
                            'id' => 69,
                            'name' => 'Valtech',
                        ),
                    ),
                ),
                'expectedAlertData' => array(
                    'companyOrLlpNo' => '03127414',
                    'name' => 'Valtech',
                    'organisation' => 69,
                    'reasons' => array(
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_STATUS_CHANGE,
                        ),
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_NAME_CHANGE,
                        ),
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_ADDRESS_CHANGE,
                        ),
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_PEOPLE_CHANGE,
                        ),
                    ),
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH 2 LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'dissolved',
                    'addressLine1' => '122 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => array(
                        array(
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                            'dateOfBirth' => '1979-02-16',
                        ),
                        array (
                            'name' => 'SMITH, John',
                            'role' => 'director',
                            'dateOfBirth' => '1969-06-13',
                        ),
                    ),
                ),
            ),
            // additional tests for various address changes
            'address field removed' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => array(),
                    'company_status' => 'active',
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(),
                    'companyStatus' => 'active',
                    'country' => null,
                ),
                'orgData' => array(
                    'Count' => 1,
                    'Results' => array(
                        array(
                            'id' => 69,
                            'name' => 'Valtech',
                        ),
                    ),

                ),
                'expectedAlertData' => array(
                    'companyOrLlpNo' => '03127414',
                    'name' => 'Valtech',
                    'organisation' => 69,
                    'reasons' => array(
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_ADDRESS_CHANGE,
                        ),
                    ),
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => array(),
                ),
            ),
            'address field added' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'locality' => 'Greater London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => array(),
                    'company_status' => 'active',
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(),
                    'companyStatus' => 'active',
                    'country' => null,
                ),
                'orgData' => array(
                    'Count' => 1,
                    'Results' => array(
                        array(
                            'id' => 69,
                            'name' => 'Valtech',
                        ),
                    ),

                ),
                'expectedAlertData' => array(
                    'companyOrLlpNo' => '03127414',
                    'name' => 'Valtech',
                    'organisation' => 69,
                    'reasons' => array(
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_ADDRESS_CHANGE,
                        ),
                    ),
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'locality' => 'Greater London',
                    'officers' => array(),
                ),
            ),
            'people role change' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => array(
                        'resigned_count' => 18,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => '1979-02-16',
                                'appointed_on' => '2008-09-15',
                            ),
                            1 => array(
                                'appointed_on' => '2011-11-14',
                                'officer_role' => 'director',
                                'name' => 'SMITH, John',
                                'date_of_birth' => '1969-06-13',
                            ),
                        ),
                        'active_count' => 2,
                    ),
                    'company_status' => 'active',
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(
                        array(
                            'dateOfBirth' => '1979-02-16',
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ),
                        array(
                            'dateOfBirth' => '1968-12-16',
                            'name' => 'SMITH, John',
                            'role' => 'secretary',
                        ),
                    ),
                    'companyStatus' => 'active',
                    'country' => null,
                ),
                'orgData' => array(
                    'Count' => 1,
                    'Results' => array(
                        array(
                            'id' => 69,
                            'name' => 'Valtech',
                        ),
                    ),

                ),
                'expectedAlertData' => array(
                    'companyOrLlpNo' => '03127414',
                    'name' => 'Valtech',
                    'organisation' => 69,
                    'reasons' => array(
                        array(
                            'reasonType' => CompaniesHouseAlertEntityService::REASON_PEOPLE_CHANGE,
                        ),
                    ),
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => array(
                        array(
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                            'dateOfBirth' => '1979-02-16',
                        ),
                        array(
                            'name' => 'SMITH, John',
                            'role' => 'director',
                            'dateOfBirth' => '1969-06-13',
                        ),
                    ),
                )
            ),
        );
    }

    public function firstTimeProvider()
    {
        return array(
            array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => array(
                        'resigned_count' => 17,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => '1979-02-16',
                                'appointed_on' => '2008-09-15',
                            ),
                            1 => array(
                                'appointed_on' => '2008-09-15',
                                'officer_role' => 'director',
                                'name' => 'HALL, Philip',
                                'date_of_birth' => '1968-12-16',
                            ),
                            2 => array(
                                'appointed_on' => '2011-11-14',
                                'officer_role' => 'director',
                                'name' => 'SKINNER, Mark James',
                                'date_of_birth' => '1969-06-13',
                            ),
                        ),
                        'active_count' => 3,
                    ),
                    'company_status' => 'active',
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => array(
                        array(
                          'name' => 'DILLON, Andrew',
                          'role' => 'director',
                          'dateOfBirth' => '1979-02-16',
                        ),
                        array(
                          'name' => 'HALL, Philip',
                          'role' => 'director',
                          'dateOfBirth' => '1968-12-16',
                        ),
                        array(
                          'name' => 'SKINNER, Mark James',
                          'role' => 'director',
                          'dateOfBirth' => '1969-06-13',
                        ),
                    ),
                ),
            ),
        );
    }

    public function orgNotFoundProvider()
    {
        // we can reuse the other data provider but only need one case
        $testCases = $this->changesProvider();
        return [array_shift($testCases)];
    }
}
