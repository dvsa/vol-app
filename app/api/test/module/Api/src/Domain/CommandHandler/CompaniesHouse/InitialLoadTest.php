<?php

declare(strict_types=1);

/**
 * Companies House Initial Load Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\InitialLoad as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse\InitialLoad;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as Repo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompanyEntity;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseApi;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 *  Companies House Initial Load Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InitialLoadTest extends AbstractCommandHandlerTestCase
{
    protected $mockApi;

    public function setUp(): void
    {
        $this->mockApi = m::mock(CompaniesHouseApi::class);
        $this->mockedSmServices = [
            CompaniesHouseApi::class => $this->mockApi
        ];
        $this->sut = new InitialLoad();
        $this->mockRepo('CompaniesHouseCompany', Repo::class);

        parent::setUp();
    }

    /**
     * Test handleCommand method happy path
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('successProvider')]
    public function testHandleCommandSuccess(mixed $companyNumber, mixed $stubResponse, mixed $expectedSaveData): void
    {
        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn($stubResponse);

        $command = Cmd::create(['companyNumber' => $companyNumber]);

        /** @var CompanyEntity $company */
        $company = null;
        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(CompanyEntity::class))
            ->andReturnUsing(
                function (CompanyEntity $co) use (&$company) {
                    $company = $co;
                    $co->setId(69);
                }
            );

        $this->sut->handleCommand($command);

        $this->assertEquals($expectedSaveData, $company->toArray());
    }

    public static function successProvider(): array
    {
        return [
            'real example' => [
                'companyNumber' => '03127414',
                'stubResponse' => [
                    'registered_office_address' => [
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ],
                    'last_full_members_list_date' => '2014-11-17',
                    'accounts' => [
                        'next_due' => '2015-09-30',
                        'last_accounts' => [
                            'type' => 'full',
                            'made_up_to' => '2013-12-31',
                        ],
                        'accounting_reference_date' => [
                            'day' => '31',
                            'month' => '12',
                        ],
                        'next_made_up_to' => '2014-12-31',
                        'overdue' => false,
                    ],
                    'date_of_creation' => '1995-11-17',
                    'sic_codes' => [
                        0 => '62020',
                    ],
                    'undeliverable_registered_office_address' => false,
                    'annual_return' => [
                        'next_due' => '2015-12-15',
                        'overdue' => false,
                        'next_made_up_to' => '2015-11-17',
                        'last_made_up_to' => '2014-11-17',
                    ],
                    'company_name' => 'VALTECH LIMITED',
                    'jurisdiction' => 'england-wales',
                    'company_number' => '03127414',
                    'type' => 'ltd',
                    'has_been_liquidated' => false,
                    'has_insolvency_history' => false,
                    'etag' => 'ec52ec76d16210d1133df1b4c9bb8f797a38d09c',
                    'officer_summary' => [
                        'resigned_count' => 17,
                        'officers' => [
                            0 => [
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => [
                                    'year' => '1979',
                                    'month' => '2',
                                ],
                            ],
                            1 => [
                                'officer_role' => 'director',
                                'name' => 'HALL, Philip',
                                'date_of_birth' => [
                                    'year' => '1968',
                                    'month' => '12',
                                ],
                            ],
                            2 => [
                                'officer_role' => 'director',
                                'name' => 'SKINNER, Mark James',
                                'date_of_birth' => [
                                    'year' => '1969',
                                    'month' => '6',
                                ],
                            ],
                        ],
                        'active_count' => 3,
                    ],
                    'company_status' => 'active',
                    'can_file' => true,
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => [
                        [
                          'name' => 'DILLON, Andrew',
                          'role' => 'director',
                          'dateOfBirth' => new \DateTime('1979-02-01'),
                        ],
                        [
                          'name' => 'HALL, Philip',
                          'role' => 'director',
                          'dateOfBirth' => new \DateTime('1968-12-01'),
                        ],
                        [
                          'name' => 'SKINNER, Mark James',
                          'role' => 'director',
                          'dateOfBirth' => new \DateTime('1969-06-01'),
                        ],
                    ],
                    'country' => null,
                    'locality' => null,
                    'poBox' => null,
                    'premises' => null,
                    'region' => null,
                    'insolvencyProcessed' => null
                ],
            ],
            'no officers' => [
                'companyNumber' => '03127414',
                'stubResponse' => [
                    'registered_office_address' => [
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ],
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => [
                        'resigned_count' => 17,
                        'officers' => null,
                        'active_count' => 0,
                    ],
                    'company_status' => 'active',
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => [],
                    'country' => null,
                    'locality' => null,
                    'poBox' => null,
                    'premises' => null,
                    'region' => null,
                    'insolvencyProcessed' => null
                ],
            ],
        ];
    }

    /**
     * Test handleCommand exception handling
     */
    public function testHandleCommandException(): void
    {
        // data
        $companyNumber = '01234567';

        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andThrow(new ServiceException('company not found'));

        $command = Cmd::create(['companyNumber' => $companyNumber]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failure from Companies House API: company not found');

        $this->sut->handleCommand($command);
    }
}
