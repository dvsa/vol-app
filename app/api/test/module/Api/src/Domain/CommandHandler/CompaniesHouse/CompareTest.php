<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\Compare as Cmd;
use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\CreateAlert as CreateAlertCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse\Compare;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as CompanyRepo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as AlertEntity;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompanyEntity;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseApi;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\NotFoundException as ChNotFoundException;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @author Dan Eggleston <dan@stolenegg.com>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse\Compare::class)]
class CompareTest extends AbstractCommandHandlerTestCase
{
    /** @var  CompaniesHouseApi | m\MockInterface */
    protected $mockApi;

    public function setUp(): void
    {
        $this->mockApi = m::mock(CompaniesHouseApi::class);
        $this->mockedSmServices = [
            CompaniesHouseApi::class => $this->mockApi
        ];

        $this->sut = new Compare();
        $this->mockRepo('CompaniesHouseCompany', CompanyRepo::class);

        parent::setUp();
    }

    /**
     * Test handleCommand method with no changes
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('noChangesProvider')]
    public function testHandleCommandNoChanges(mixed $companyNumber, mixed $stubResponse, mixed $stubSavedData): void
    {
        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn($stubResponse);

        /** @var CompanyEntity $company */
        $company = new CompanyEntity($stubSavedData);
        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn($company);

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertEquals(['No changes'], $result->getMessages());
    }

    /**
     * Test handleCommand method when company not found
     */
    public function testHandleCommandCompanyNotFound(): void
    {
        // data
        $companyNumber = '01234567';

        $expectedAlertData = [
            'companyNumber' => $companyNumber,
            'reasons' => [
                AlertEntity::REASON_INVALID_COMPANY_NUMBER,
            ],
        ];

        $alertResult = new Result();
        $alertResult
            ->addId('companiesHouseAlert', 101)
            ->addMessage('Alert created');

        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andThrow(new ChNotFoundException('not found!'));

        $this->expectedSideEffect(CreateAlertCmd::class, $expectedAlertData, $alertResult);

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(['Alert created'], $result->getMessages());
        $this->assertEquals(['companiesHouseAlert' => 101], $result->getIds());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandValidiateCompanyNumberDataProvider')]
    public function testHandleCommandValidiateCompanyNumber(mixed $expectValid, mixed $companyNumber): void
    {
        $expectedAlertData = [
            'companyNumber' => $companyNumber,
            'reasons' => [
                AlertEntity::REASON_INVALID_COMPANY_NUMBER,
            ],
        ];

        $alertResult = new Result();
        $alertResult
            ->addId('companiesHouseAlert', 101)
            ->addMessage('Alert created');

        if ($expectValid) {
            // Company number is valid, throw this exception to stop execution as we only want to test
            // the company number validation
            $this->mockApi
                ->shouldReceive('getCompanyProfile')->with($companyNumber, true)->once()
                ->andThrow(new \Exception('Company number validation passsed'));
            $this->expectException(NotReadyException::class);
        } else {
            $this->expectedSideEffect(
                CreateAlertCmd::class,
                [
                    'companyNumber' => $companyNumber,
                    'reasons' => [
                        AlertEntity::REASON_INVALID_COMPANY_NUMBER,
                    ]
                ],
                new Result()
            );
        }

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $this->sut->handleCommand($command);
    }

    public static function handleCommandValidiateCompanyNumberDataProvider(): array
    {
        return [
            [true, '6'],
            [true, '6563254723654732645'],
            [true, 'A'],
            [true, 'a'],
            [true, 'AbCdEfGhIjKlMnOpQr'],
            [true, 'SCO12345'],
            [false, 'SCO 12345'],
            [false, '.SCO 12345'],
            [false, '.SCO12345'],
            [false, '. 526353'],
        ];
    }

    /**
     * Test handleCommand method when company was not previously stored
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('firstTimeProvider')]
    public function testHandleCommandCompanyFirstTimeFound(mixed $companyNumber, mixed $stubResponse, mixed $expectedSaveData): void
    {
        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn($stubResponse);

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andThrow(new NotFoundException('company not found'));

        // invoke
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

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertEquals($expectedSaveData, $company->toArray());
        $this->assertEquals(['companiesHouseCompany' => 69], $result->getIds());
        $this->assertEquals(['Saved new company'], $result->getMessages());
    }

    /**
     * Test handleCommand method
     *
     *
     * @param string $companyNumber
     * @param array  $stubResponse     api response
     * @param array  $stubSavedData    last saved company data
     * @param array  $expectedAlertData
     * @param array  $expectedSaveData new company data to save
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('changesProvider')]
    public function testHandleCommandChanges(
        mixed $companyNumber,
        mixed $stubResponse,
        mixed $stubSavedData,
        mixed $expectedAlertData,
        array $expectedSaveData = []
    ): void {
        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn($stubResponse);

        /** @var CompanyEntity $company */
        $company = new CompanyEntity($stubSavedData);
        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn($company);

        /** @var CompanyEntity $newCompany */
        $newCompany = null;
        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(CompanyEntity::class))
            ->andReturnUsing(
                function (CompanyEntity $co) use (&$newCompany) {
                    $newCompany = $co;
                    $co->setId(69);
                }
            );

        $alertResult = new Result();
        $alertResult
            ->addId('companiesHouseAlert', 123)
            ->addMessage('Alert created');
        $this->expectedSideEffect(CreateAlertCmd::class, $expectedAlertData, $alertResult);

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $this->sut->handleCommand($command);

        $this->assertEquals($expectedSaveData, $newCompany->toArray());
    }

    public static function noChangesProvider(): array
    {
        return [
            'no changes' => [
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
                                    'month' => '02',
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
                                    'month' => '06',
                                ],
                            ],
                        ],
                        'active_count' => 3,
                    ],
                    'company_status' => 'active',
                    'can_file' => true,
                ],
                'stubSavedData' => [
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
                    'officers' => [
                        [
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                            'name' => 'DILLON, Andrew',
                            'role' => 'director'
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1968-12-01'),
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1969-06-01'),
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ],
                    ],
                    'companyStatus' => 'active',
                    'country' => null,
                ]
            ],
        ];
    }

    public static function firstTimeProvider(): array
    {
        return [
            [
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
                        'officers' => [
                            0 => [
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => [
                                    'year' => '1979',
                                    'month' => '02',
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
                                    'month' => '06',
                                ],
                            ],
                        ],
                        'active_count' => 3,
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
        ];
    }

    /**
     * @return array
     */
    public static function changesProvider(): array
    {
        return [
            'non_insolvent_status_change' => [
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
                                    'month' => '02',
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
                                    'month' => '06',
                                ],
                            ],
                        ],
                        'active_count' => 3,
                    ],
                    'company_status' => 'dissolved',
                    'can_file' => true,
                ],
                'stubSavedData' => [
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
                    'officers' => [
                        [
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1968-12-01'),
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1969-06-01'),
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ],
                    ],
                    'companyStatus' => 'active',
                    'country' => null,
                ],
                'expectedAlertData' => [
                    'companyNumber' => '03127414',
                    'reasons' => [
                        AlertEntity::REASON_STATUS_CHANGE,
                    ],
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'dissolved',
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
                    'insolvencyProcessed' => 0
                ],
            ],
            'name status address and people change' => [
                'companyNumber' => '03127414',
                'stubResponse' => [
                    'registered_office_address' => [
                        'address_line_1' => '122 Aldersgate Street',
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
                    'company_name' => 'VALTECH 2 LIMITED',
                    'jurisdiction' => 'england-wales',
                    'company_number' => '03127414',
                    'type' => 'ltd',
                    'has_been_liquidated' => false,
                    'has_insolvency_history' => false,
                    'etag' => 'ec52ec76d16210d1133df1b4c9bb8f797a38d09c',
                    'officer_summary' => [
                        'resigned_count' => 18,
                        'officers' => [
                            0 => [
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => [
                                    'year' => '1979',
                                    'month' => '02',
                                ],
                            ],
                            1 => [
                                'officer_role' => 'director',
                                'name' => 'SMITH, John',
                                'date_of_birth' => [
                                    'year' => '1969',
                                    'month' => '06',
                                ],
                            ],
                        ],
                        'active_count' => 2,
                    ],
                    'company_status' => 'dissolved',
                    'can_file' => true,
                ],
                'stubSavedData' => [
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
                    'officers' => [
                        [
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1968-12-01'),
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1969-06-01'),
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ],
                    ],
                    'companyStatus' => 'active',
                    'country' => null,
                ],
                'expectedAlertData' => [
                    'companyNumber' => '03127414',
                    'reasons' => [
                        AlertEntity::REASON_STATUS_CHANGE,
                        AlertEntity::REASON_NAME_CHANGE,
                        AlertEntity::REASON_ADDRESS_CHANGE,
                        AlertEntity::REASON_PEOPLE_CHANGE,
                    ],
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH 2 LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'dissolved',
                    'addressLine1' => '122 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => [
                        [
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                        ],
                        [
                            'name' => 'SMITH, John',
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
            // additional tests for various address changes
            'address field removed' => [
                'companyNumber' => '03127414',
                'stubResponse' => [
                    'registered_office_address' => [
                        'address_line_1' => '120 Aldersgate Street',
                        'postal_code' => 'EC1A 4JQ',
                    ],
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => [],
                    'company_status' => 'active',
                ],
                'stubSavedData' => [
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
                    'officers' => [],
                    'companyStatus' => 'active',
                    'country' => null,
                ],
                'expectedAlertData' => [
                    'companyNumber' => '03127414',
                    'reasons' => [
                        AlertEntity::REASON_ADDRESS_CHANGE,
                    ],
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => null,
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
            'address field added' => [
                'companyNumber' => '03127414',
                'stubResponse' => [
                    'registered_office_address' => [
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'locality' => 'Greater London',
                        'postal_code' => 'EC1A 4JQ',
                    ],
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => [],
                    'company_status' => 'active',
                ],
                'stubSavedData' => [
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
                    'officers' => [],
                    'companyStatus' => 'active',
                    'country' => null,
                ],
                'expectedAlertData' => [
                    'companyNumber' => '03127414',
                    'reasons' => [
                        AlertEntity::REASON_ADDRESS_CHANGE,
                    ],
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'locality' => 'Greater London',
                    'officers' => [],
                    'country' => null,
                    'poBox' => null,
                    'premises' => null,
                    'region' => null,
                    'insolvencyProcessed' => null
                ],
            ],
            'people role change' => [
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
                        'resigned_count' => 18,
                        'officers' => [
                            0 => [
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => [
                                    'year' => '1979',
                                    'month' => '02',
                                ],
                            ],
                            1 => [
                                'officer_role' => 'director',
                                'name' => 'SMITH, John',
                                'date_of_birth' => [
                                    'year' => '1969',
                                    'month' => '06',
                                ],
                            ],
                        ],
                        'active_count' => 2,
                    ],
                    'company_status' => 'active',
                ],
                'stubSavedData' => [
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
                    'officers' => [
                        [
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1968-12-01'),
                            'name' => 'SMITH, John',
                            'role' => 'secretary',
                        ],
                    ],
                    'companyStatus' => 'active',
                    'country' => null,
                ],
                'expectedAlertData' => [
                    'companyNumber' => '03127414',
                    'reasons' => [
                        AlertEntity::REASON_PEOPLE_CHANGE,
                    ],
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
                            'name' => 'SMITH, John',
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
                ]
            ],
            'insolvent_status_change' => [
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
                                    'month' => '02',
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
                                    'month' => '06',
                                ],
                            ],
                        ],
                        'active_count' => 3,
                    ],
                    'company_status' => 'administration',
                    'can_file' => true,
                ],
                'stubSavedData' => [
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
                    'officers' => [
                        [
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                            'name' => 'DILLON, Andrew',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1968-12-01'),
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ],
                        [
                            'dateOfBirth' => new \DateTime('1969-06-01'),
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ],
                    ],
                    'companyStatus' => 'active',
                    'country' => null,
                ],
                'expectedAlertData' => [
                    'companyNumber' => '03127414',
                    'reasons' => [
                        AlertEntity::REASON_STATUS_CHANGE,
                    ],
                ],
                'expectedSaveData' => [
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'administration',
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
                    'insolvencyProcessed' => 0
                ],
            ],
        ];
    }

    public function testHandleCommandServiceError(): void
    {
        $companyNr = 9999;

        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->andThrowExceptions([new \Exception('unit_error_message')]);

        $this->expectException(
            NotReadyException::class
        );

        $command = Cmd::create(['companyNumber' => $companyNr]);
        $this->sut->handleCommand($command);
    }
}
