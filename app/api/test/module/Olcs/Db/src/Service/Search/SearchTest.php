<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Db\Service\Search;

use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Db\Service\Search\Search as SearchService;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenSearch\Client;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Db\Service\Search\Search::class)]
final class SearchTest extends MockeryTestCase
{
    /** @var  SearchService */
    private $sut;

    /** @var  m\MockInterface | AuthorizationService */
    private $mockAuthSrv;
    /** @var  m\MockInterface | SystemParameter */
    private $mockSPRepo;
    /** @var  m\MockInterface | Client */
    private $mockClient;
    /** @var  m\MockInterface | \Dvsa\Olcs\Api\Entity\User\User */
    private $mockUser;

    public function setUp(): void
    {
        $this->mockClient = m::mock(Client::class);
        $this->mockAuthSrv = m::mock(AuthorizationService::class);
        $this->mockSPRepo = m::mock(SystemParameter::class);
        $this->mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();

        $this->sut = new SearchService($this->mockClient, $this->mockAuthSrv, $this->mockSPRepo);

        $this->mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($this->mockUser);
        $this->mockUser->shouldReceive('getUser')->andReturnSelf();

        $mockedSmServices = [
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function filterFunctionalityDataProvider(): \Iterator
    {
        yield [
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
        ];
    }

    public function testUpdateVehicleSection26(): void
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

        $this->mockClient->expects('search')
            ->with(['index' => 'vehicle_current,vehicle_removed', 'body' => $expectedQuery])
            ->andReturn(
                [
                    'hits' => [
                        'total' => ['value' => 2, 'relation' => 'eq'],
                        'hits' => [
                            ['_index' => 'vehicle_current_v1', '_id' => 'zz', '_source' => ['veh_id' => 511]],
                            ['_index' => 'vehicle_removed_v1', '_id' => 'yy', '_source' => ['veh_id' => 2015]],
                        ],
                    ],
                ]
            );

        $this->mockClient->expects('bulk')
            ->with(
                [
                    'body' => [
                        ['update' => ['_index' => 'vehicle_current_v1', '_id' => 'zz']],
                        ['doc' => ['section_26' => 1]],
                        ['update' => ['_index' => 'vehicle_removed_v1', '_id' => 'yy']],
                        ['doc' => ['section_26' => 1]],
                    ],
                ]
            )
            ->andReturn(['took' => 1, 'errors' => false, 'items' => []]);

        $this->assertTrue($this->sut->updateVehicleSection26($ids, $section26Value));
    }

    public function testUpdateVehicleSection26ThrowsWhenBulkReportsItemErrors(): void
    {
        $this->mockClient->expects('search')->andReturn(
            ['hits' => ['hits' => [['_index' => 'vehicle_current_v1', '_id' => 'zz', '_source' => []]]]]
        );
        $this->mockClient->expects('bulk')
            ->with(
                [
                    'body' => [
                        ['update' => ['_index' => 'vehicle_current_v1', '_id' => 'zz']],
                        ['doc' => ['section_26' => 0]],
                    ],
                ]
            )
            ->andReturn([
                'took' => 1,
                'errors' => true,
                'items' => [
                    ['update' => ['_index' => 'vehicle_current_v1', '_id' => 'zz', 'status' => 409, 'error' => ['type' => 'version_conflict_engine_exception']]],
                ],
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('version_conflict_engine_exception');

        $this->sut->updateVehicleSection26([511], false);
    }

    public function testUpdateVehicleSection26WithNoIdsDoesNotTouchTheIndex(): void
    {
        $this->mockClient->shouldNotReceive('search');
        $this->mockClient->shouldNotReceive('bulk');

        $this->assertTrue($this->sut->updateVehicleSection26([], true));
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

        $this->mockClient->expects('search')
            ->with(['index' => 'vehicle_current,vehicle_removed', 'body' => $expectedQuery])
            ->andReturn(['hits' => ['total' => ['value' => 0, 'relation' => 'eq'], 'hits' => []]]);
        $this->mockClient->shouldNotReceive('bulk');

        $this->assertTrue($this->sut->updateVehicleSection26($ids, $section26Value));
    }

    public static function internalSearchDataProvider(): \Iterator
    {
        yield [ '1,112', 1, 17, 'C'];
        yield [ '1,112', 1, 17, 'N'];
        yield [ '1,112,17', 0, 17, 'N'];
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

        $this->mockClient->expects('search')->andReturnUsing(
            function (array $params) use ($taCheckTimes, $trafficAreaId) {
                $this->assertSame('licence', $params['index']);

                $body = $params['body'];
                $this->assertArrayHasKey('query', $body);
                $this->assertSame(['foo' => 'desc'], $body['sort']);
                $this->assertSame(0, $body['from']);
                $this->assertSame(10, $body['size']);

                if ($taCheckTimes === 0) {
                    $this->assertArrayNotHasKey('post_filter', $body);
                } else {
                    $disallowed = $trafficAreaId === 'N' ? TrafficArea::GB_TA_IDS : TrafficArea::NI_TA_IDS;
                    $this->assertSame(
                        array_map(fn($taId) => ['match' => ['ta_id' => $taId]], $disallowed),
                        $body['post_filter']['bool']['must_not']
                    );
                    $this->assertArrayNotHasKey('must', $body['post_filter']['bool']);
                }

                return [];
            }
        );

        $this->sut->setSort('foo');
        $this->sut->setOrder('desc');

        $this->sut->search('FOO', ['licence']);
    }

    public function testSearchIndexInternalApplicationAddsNiFlagToPostFilter(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(true)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(false);

        $this->mockSPRepo->shouldReceive('fetchValue')->with(\Dvsa\Olcs\Api\Entity\System\SystemParameter::DATA_SEPARATION_TEAMS_EXEMPT)->once()->andReturn('1');
        $this->mockUser->shouldReceive('getTeam->getId')->once()->andReturn(17);
        $this->mockUser->shouldReceive('getTeam->getTrafficArea->getId')->once()->andReturn('N');

        $this->mockClient->expects('search')->andReturnUsing(
            function (array $params) {
                $this->assertSame('application', $params['index']);
                $this->assertSame(
                    [['match' => ['ni_flag' => true]]],
                    $params['body']['post_filter']['bool']['must']
                );

                return [];
            }
        );

        $this->sut->search('FOO', ['application']);
    }

    public function testSearchIndexExternal(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $this->mockClient->expects('search')->andReturnUsing(
            function (array $params) {
                $this->assertSame('licence', $params['index']);

                $body = $params['body'];
                $this->assertArrayHasKey('query', $body);
                $this->assertSame(['foo' => 'desc'], $body['sort']);
                $this->assertSame(0, $body['from']);
                $this->assertSame(10, $body['size']);
                $this->assertArrayNotHasKey('post_filter', $body);
                $this->assertSame(
                    [
                        'organisation_name' => [
                            'terms' => ['field' => 'organisation_name', 'order' => ['_key' => 'asc'], 'size' => 25],
                        ],
                        'licence_traffic_area' => [
                            'terms' => ['field' => 'licence_traffic_area', 'order' => ['_key' => 'asc'], 'size' => 25],
                        ],
                    ],
                    $body['aggs']
                );

                return [];
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

        $this->mockClient->expects('search')->andReturnUsing(
            function (array $params) {
                $this->assertSame('licence', $params['index']);

                $body = $params['body'];
                $this->assertArrayHasKey('query', $body);
                $this->assertSame(['foo' => 'desc'], $body['sort']);
                $this->assertSame(0, $body['from']);
                $this->assertSame(10, $body['size']);
                $this->assertArrayNotHasKey('aggs', $body);
                $this->assertArrayNotHasKey('post_filter', $body);

                return [];
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

    public static function setDateRangesDataProvider(): \Iterator
    {
        // valid from string
        yield [
            [
                'field1' => '2010-02-01',
            ],
            [
                'field1' => '2010-02-01',
            ]
        ];
        // valid from array
        yield [
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
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidDateProvider')]
    public function testInvalidDateFilter(mixed $data): void
    {
        $this->expectException(\Dvsa\Olcs\Db\Exceptions\SearchDateFilterParseException::class);

        $this->sut->setDateRanges($data);
    }

    public static function invalidDateProvider(): \Iterator
    {
        yield [
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
        ];
    }

    public function testSearchUnderMaxResults(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $this->mockClient->expects('search')->andReturn(
            ['hits' => ['total' => ['value' => SearchService::MAX_NUMBER_OF_RESULTS - 1, 'relation' => 'eq'], 'hits' => []]]
        );

        $result = $this->sut->search('FOO', ['licence']);

        $this->assertSame(SearchService::MAX_NUMBER_OF_RESULTS - 1, $result['Count']);
    }

    public function testSearchOverMaxResults(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $this->mockClient->expects('search')->andReturn(
            ['hits' => ['total' => ['value' => SearchService::MAX_NUMBER_OF_RESULTS + 1, 'relation' => 'gte'], 'hits' => []]]
        );

        $result = $this->sut->search('FOO', ['licence']);
        $this->assertSame(SearchService::MAX_NUMBER_OF_RESULTS, $result['Count']);
    }

    public function testSearchShapesHitsAndAggregationsIntoTheResponse(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(false);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);

        $this->mockClient->expects('search')->andReturn(
            [
                'hits' => [
                    'total' => ['value' => 1, 'relation' => 'eq'],
                    'hits' => [
                        ['_index' => 'licence_v1', '_id' => '7', '_source' => ['lic_no' => 'OB1234567', 'org_name' => 'Acme']],
                    ],
                ],
                'aggregations' => [
                    'licence_traffic_area' => [
                        'buckets' => [['key' => 'B', 'doc_count' => 1]],
                    ],
                ],
            ]
        );

        $this->sut->setFilters(['licenceTrafficArea' => 'B']);
        $result = $this->sut->search('FOO', ['licence']);

        $this->assertSame(1, $result['Count']);
        $this->assertSame([['licNo' => 'OB1234567', 'orgName' => 'Acme']], $result['Results']);
        $this->assertSame(['licenceTrafficArea' => [['key' => 'B', 'doc_count' => 1]]], $result['Filters']);
    }

    public function testSearchWithAnEmptyResponseReturnsNoResults(): void
    {
        $this->mockUser->shouldReceive('isAnonymous')->zeroOrMoreTimes()->andReturn(true);

        $this->mockAuthSrv
            ->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)
            ->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(false);

        $this->mockClient->expects('search')->andReturn([]);

        $this->assertSame(
            ['Count' => 0, 'Results' => [], 'Filters' => []],
            $this->sut->search('FOO', ['licence'])
        );
    }
}
