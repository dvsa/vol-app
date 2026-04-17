<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Db\Service\Search;

use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Db\Service\Search\Search as SearchService;
use Elastica\Request;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Db\Service\Search\Search::class)]
class SearchTest extends MockeryTestCase
{
    /**
     * @var array<string, (\LmcRbacMvc\Service\AuthorizationService & \Mockery\MockInterface)>
     */
    public $mockedSmServices;
    /** @var  SearchService */
    private $sut;

    /** @var  m\MockInterface | AuthorizationService */
    private $mockAuthSrv;
    /** @var  m\MockInterface | SystemParameter */
    private $mockSPRepo;
    /** @var  m\MockInterface | \Elastica\Client */
    private $mockClient;
    /** @var  m\MockInterface | \Dvsa\Olcs\Api\Entity\User\User */
    private $mockUser;

    public function setUp(): void
    {
        $this->mockClient = m::mock(\Elastica\Client::class);
        $this->mockAuthSrv = m::mock(AuthorizationService::class);
        $this->mockSPRepo = m::mock(SystemParameter::class);
        $this->mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();

        $this->sut = new SearchService($this->mockClient, $this->mockAuthSrv, $this->mockSPRepo);

        $this->mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($this->mockUser);
        $this->mockUser->shouldReceive('getUser')->andReturnSelf();

        $this->mockedSmServices = [
            AuthorizationService::class => $this->mockAuthSrv,
        ];

        parent::setUp();
    }

    /**
     * Tests the filter methods and functionality.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('filterFunctionalityDataProvider')]
    public function testFilterFunctionality(array $setFilters, array $getFilters, array $filterNames): void
    {
        // Uses fluent interface to test
        $this->assertEquals($getFilters, $this->sut->setFilters($setFilters)->getFilters());

        //die(var_export($systemUnderTest->getFilterNames(), 1));

        $this->assertEquals($filterNames, $this->sut->getFilterNames());
    }

    /**
     * Data provider for testFilterFunctionality test.
     *
     * @return array
     */
    public static function filterFunctionalityDataProvider(): array
    {
        return [
            [
                'setFilters' => [
                    'organisationName' => 'a',
                    'licenceTrafficArea' => 'b',
                ],
                'getFilters' => [
                    'organisation_name' => 'a',
                    'licence_traffic_area' => 'b',
                ],
                'filterNames' => [
                    'organisation_name',
                    'licence_traffic_area',
                ],
            ],
        ];
    }

    public function updateVehicleSection26(): void
    {
        $ids = [511, 2015];
        $section26Value = true;

        $expectedQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['veh_id' => 511]],
                        ['match' => ['veh_id' => 2015]],
                    ]
                ]
            ],
            'size' => 1000,
        ];

        $searchResponse = m::mock(\Elastica\Response::class);
        $searchResponse->shouldReceive('getData')->andReturn(
            [
                'hits' => [
                    'hits' => [
                        ['id' => 'zz']
                    ]
                ]
            ]
        );

        $this->mockClient->shouldReceive('request')
            ->with('vehicle_current,vehicle_removed/_search', 'POST', $expectedQuery, [])
            ->once()
            ->andReturn($searchResponse);

        $bulkResponse = m::mock(\Elastica\Response::class);
        $bulkResponse->shouldReceive('getData');
        $bulkResponse->shouldReceive('getStatus');
        $bulkResponse->shouldReceive('getQueryTime');
        $bulkResponse->shouldReceive('getTransferInfo')->andReturn([]);

        $this->mockClient->shouldReceive('request')
            ->with(
                '_bulk',
                'POST',
                '{"update":{"_id":{},"_type":{},"_index":{}}}' . "\n" . '{"doc":{"section_26":1}}' . "\n",
                [],
                Request::NDJSON_CONTENT_TYPE
            )
            ->once()
            ->andReturn($bulkResponse);

        $this->sut->updateVehicleSection26($ids, $section26Value);
    }

    public function testUpdateVehicleSection26NoResults(): void
    {
        $ids = [511, 2015];
        $section26Value = true;

        $expectedQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['veh_id' => 511]],
                        ['match' => ['veh_id' => 2015]],
                    ]
                ]
            ],
            'size' => 1000,
        ];

        $searchResponse = m::mock(\Elastica\Response::class);
        $searchResponse->shouldReceive('getData')->andReturn([]);

        $this->mockClient->shouldReceive('request')
            ->with('vehicle_current,vehicle_removed/_search', 'POST', $expectedQuery, [])->once()
            ->andReturn($searchResponse);

        $this->sut->updateVehicleSection26($ids, $section26Value);
    }

    public static function internalSearchDataProvider(): array
    {
        return
            [
                [ '1,112', 1, 17, 'C'],
                [ '1,112', 1, 17, 'N'],
                [ '1,112,17', 0, 17, 'N']
            ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('internalSearchDataProvider')]
    public function testSearchIndexInternal(mixed $excludedTeamIds, mixed $taCheckTimes, mixed $teamId, mixed $trafficAreaId): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(true)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(false);

        $this->mockSPRepo->shouldReceive('fetchValue')->with(\Dvsa\Olcs\Api\Entity\System\SystemParameter::DATA_SEPARATION_TEAMS_EXEMPT)->once()->andReturn($excludedTeamIds);
        $this->mockUser->shouldReceive('getTeam->getId')->once()->andReturn($teamId);
        $this->mockUser->shouldReceive('getTeam->getTrafficArea->getId')->times($taCheckTimes)->andReturn($trafficAreaId);

        $this->mockClient->shouldReceive('request')->once()->andReturnUsing(
            function ($path, $method, $query, $params) {
                $this->assertSame('licence/_search', $path);
                $this->assertSame('POST', $method);

                $this->assertArrayHasKey('query', $query);
                $this->assertSame(['foo' => 'desc'], $query['sort']);
                $this->assertSame(0, $query['from']);
                $this->assertSame(10, $query['size']);
                $this->assertSame([], $params);

                $searchResponse = m::mock(\Elastica\Response::class);
                $searchResponse->shouldReceive('getData')->andReturn([]);
                return $searchResponse;
            }
        );

        $this->sut->setSort('foo');
        $this->sut->setOrder('desc');

        $this->sut->search('FOO', ['licence']);
    }

    public function testSearchIndexExternal(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $this->mockClient->shouldReceive('request')->once()->andReturnUsing(
            function ($path, $method, $query, $params) {
                $this->assertSame('licence/_search', $path);
                $this->assertSame('POST', $method);

                $this->assertArrayHasKey('query', $query);
                $this->assertSame(['foo' => 'desc'], $query['sort']);
                $this->assertSame(0, $query['from']);
                $this->assertSame(10, $query['size']);
                $this->assertSame([], $params);

                $searchResponse = m::mock(\Elastica\Response::class);
                $searchResponse->shouldReceive('getData')->andReturn([]);
                return $searchResponse;
            }
        );

        $this->sut->setSort('foo');
        $this->sut->setOrder('desc');
        $this->sut->setFilters(
            [
                'organisationName' => 'a',
                'licenceTrafficArea' => 'b',
            ]
        );

        $this->sut->search('FOO', ['licence']);
    }

    public function testSearchIndexAnon(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(true);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(false);

        $this->mockClient->shouldReceive('request')->once()->andReturnUsing(
            function ($path, $method, $query, $params) {
                $this->assertSame('licence/_search', $path);
                $this->assertSame('POST', $method);

                $this->assertArrayHasKey('query', $query);
                $this->assertSame(['foo' => 'desc'], $query['sort']);
                $this->assertSame(0, $query['from']);
                $this->assertSame(10, $query['size']);
                $this->assertSame([], $params);

                $searchResponse = m::mock(\Elastica\Response::class);
                $searchResponse->shouldReceive('getData')->andReturn([]);
                return $searchResponse;
            }
        );

        $this->sut->setSort('foo');
        $this->sut->setOrder('desc');

        $this->sut->search('FOO', ['licence']);
    }

    public function testSearchIndexQueryTemplateNotFound(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(true)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $this->expectException(
            \RuntimeException::class
        );

        $this->sut->setSort('foo');
        $this->sut->setOrder('desc');

        $this->sut->search('FOO', ['MISSING']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('setDateRangesDataProvider')]
    public function testSetDateRanges(mixed $data, mixed $expect): void
    {
        $this->sut->setDateRanges($data);

        $this->assertSame($expect, $this->sut->getDateRanges());
    }

    public static function setDateRangesDataProvider(): array
    {
        return [
            // valid from string
            [
                [
                    'field1' => '2010-02-01',
                ],
                [
                    'field1' => '2010-02-01',
                ]
            ],
            // valid from array
            [
                [
                    'field1' => [
                        'year' => '2010',
                        'month' => '02',
                        'day' => '01',
                    ],
                    'field2' => [
                        'year' => '2010',
                        'month' => '2',
                        'day' => '1',
                    ],
                ],
                [
                    'field1' => '2010-02-01',
                    'field2' => '2010-02-01',
                ]
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidDateProvider')]
    public function testInvalidDateFilter(mixed $data): void
    {
        $this->expectException(\Dvsa\Olcs\Db\Exceptions\SearchDateFilterParseException::class);

        $this->sut->setDateRanges($data);
    }

    public static function invalidDateProvider(): array
    {
        return    // invalid
            [
                [
                    [
                        'field6' => [
                            'year' => '2010',
                            'month' => '02',
                            'day' => '',
                        ],
                        'field7' => [
                            'year' => '',
                            'month' => '02',
                            'day' => '01',
                        ],
                        'field8' => [
                            'year' => '2010',
                            'month' => '13',
                            'day' => '01',
                        ],
                        'field9' => [
                            'year' => '2017',
                            'month' => '02',
                            'day' => '',
                        ],
                        'field10' => [
                            'year' => '2010',
                            'month' => '02',
                            'day' => '53',
                        ],

                    ],
                ]
            ];
    }

    # VOL-3447 - Evaluate this test and reinstate/update/delete as appropriate
    public function searchUnderMaxResults(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $searchResponse = m::mock(\Elastica\Response::class);
        $searchResponse->shouldReceive('getData')->andReturn(
            ['hits' => ['total' => SearchService::MAX_NUMBER_OF_RESULTS - 1]]
        );
        $this->mockClient->shouldReceive('request')->once()->andReturn($searchResponse);

        $result = $this->sut->search('FOO', ['licence']);

        $this->assertSame(SearchService::MAX_NUMBER_OF_RESULTS - 1, $result['Count']);
    }

    # VOL-3447 - Evaluate this test and reinstate/update/delete as appropriate
    public function searchOverMaxResults(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $searchResponse = m::mock(\Elastica\Response::class);
        $searchResponse->shouldReceive('getData')->andReturn(
            ['hits' => ['total' => SearchService::MAX_NUMBER_OF_RESULTS + 1]]
        );
        $this->mockClient->shouldReceive('request')->once()->andReturn($searchResponse);

        $result = $this->sut->search('FOO', ['licence']);
        $this->assertSame(SearchService::MAX_NUMBER_OF_RESULTS, $result['Count']);
    }
}
