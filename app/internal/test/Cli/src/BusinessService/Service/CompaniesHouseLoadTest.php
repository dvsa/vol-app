<?php

/**
 * Companies House Load Business Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CliTest\BusinessService\Service;

use Common\BusinessService\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;
use Cli\BusinessService\Service\CompaniesHouseLoad;
use Common\Service\Entity\InspectionRequestEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Exception\ResourceNotFoundException;

/**
 *  Companies House Load Business Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InspectionRequestUpdateTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new CompaniesHouseLoad();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test process method
     *
     * @dataProvider successProvider
     */
    public function testProcessSuccess($companyNumber, $stubResponse, $expectedSaveData)
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

        $saveResult = ['id' => 99];
        $mockEntityService
            ->shouldReceive('saveNew')
            ->once()
            ->with($expectedSaveData)
            ->andReturn($saveResult);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_SUCCESS, $result->getType());
        $this->assertEquals("Saved company id 99", $result->getMessage());
        $this->assertEquals($saveResult, $result->getData());
    }

    public function successProvider()
    {
        return array(
            'real example' => array(
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
            'no officers' => array(
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
                        'officers' => null,
                        'active_count' => 0,
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
                    'officers' => array(),
                ),
            ),
        );
    }

    /**
     * Test process method when company not found
     */
    public function testProcessCompanyNotFound()
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
            ->andReturn(false);

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_FAILED, $result->getType());
        $this->assertEquals("Company not found", $result->getMessage());
    }

    /**
     * Test process exception handling
     */
    public function testProcessException()
    {
        // data
        $companyNumber = '01234567';

        $stubResponse =  array(
            'registered_office_address' => array(
                'address_line_1' => '120 Aldersgate Street',
                'address_line_2' => 'London',
                'postal_code' => 'EC1A 4JQ',
            ),
            'company_name' => 'VALTECH LIMITED',
            'company_number' => '03127414',
            'officer_summary' => array(
                'resigned_count' => 17,
                'officers' => null,
                'active_count' => 0,
            ),
            'company_status' => 'active',
        );

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
            ->shouldReceive('saveNew')
            ->once()
            ->andThrow(new \Exception('oops!'));

        // invoke
        $params = ['companyNumber' => $companyNumber];
        $result = $this->sut->process($params);

        // assertions
        $this->assertInstanceOf('Common\BusinessService\Response', $result);
        $this->assertEquals(Response::TYPE_FAILED, $result->getType());
        $this->assertEquals("oops!", $result->getMessage());
    }
}
