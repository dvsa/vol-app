<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\EventHistory as Repo;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * EventHistoryTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class EventHistoryTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByOrganisation('ORG1'));

        $expectedQuery = 'BLAH AND m.organisation = [[ORG1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByTransportManager(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByTransportManager('TM1'));

        $expectedQuery = 'BLAH AND m.transportManager = [[TM1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByAccount(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByAccount('USER', 'EHT', 'SORT', 'ORDER', 1));

        $expectedQuery = 'BLAH AND m.account = [[USER]] AND m.eventHistoryType = [[EHT]] ORDER BY m.SORT ORDER LIMIT 1';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByAccountWithoutEventType(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByAccount('USER'));

        $expectedQuery = 'BLAH AND m.account = [[USER]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $caseId = 1;
        $licenceId = 2;
        $organisationId = 3;
        $transportManagerId = 4;
        $userId = 5;
        $applicationId = 6;
        $irhpApplicationId = 7;

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getLicence')
            ->andReturn($licenceId)
            ->twice()
            ->shouldReceive('getCase')
            ->andReturn($caseId)
            ->twice()
            ->shouldReceive('getOrganisation')
            ->andReturn($organisationId)
            ->twice()
            ->shouldReceive('getTransportManager')
            ->andReturn($transportManagerId)
            ->twice()
            ->shouldReceive('getUser')
            ->andReturn($userId)
            ->twice()
            ->shouldReceive('getApplication')
            ->andReturn($applicationId)
            ->twice()
            ->shouldReceive('getIrhpApplication')
            ->andReturn($irhpApplicationId)
            ->twice();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr->eq')->with('m.licence', ':licenceId')->once()->andReturn('licence');
        $qb->shouldReceive('orWhere')->with('licence')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licenceId', $licenceId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.case', ':caseId')->once()->andReturn('case');
        $qb->shouldReceive('orWhere')->with('case')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('caseId', $caseId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.organisation', ':organisationId')->once()->andReturn('organisation');
        $qb->shouldReceive('orWhere')->with('organisation')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('organisationId', $organisationId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')
            ->with('m.transportManager', ':transportManagerId')->once()->andReturn('transportManager');
        $qb->shouldReceive('orWhere')->with('transportManager')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('transportManagerId', $transportManagerId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.user', ':userId')->once()->andReturn('user');
        $qb->shouldReceive('orWhere')->with('user')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('userId', $userId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.application', ':applicationId')->once()->andReturn('application');
        $qb->shouldReceive('orWhere')->with('application')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('applicationId', $applicationId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.irhpApplication', ':irhpApplicationId')->once()->andReturn('irhpApplication');
        $qb->shouldReceive('orWhere')->with('irhpApplication')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('irhpApplicationId', $irhpApplicationId)->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('eventHistoryType')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withUser')->once()->andReturnSelf();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testFetchEventHistoryDetails(): void
    {
        $table = 'application_hist';
        $id = 1;
        $version = 2;
        $results = [
            [
                'foo' => 'bar2',
                'cake' => 'baz2',
                'same' => 'value',
                'version' => 2
            ],
            [
                'foo' => 'bar1',
                'cake' => 'baz1',
                'same' => 'value',
                'version' => 1
            ]
        ];

        $this->dbQueryService
            ->shouldReceive('get')
            ->with('EventHistory\GetEventHistoryDetails')
            ->andReturn(
                m::mock()
                ->shouldReceive('execute')
                ->with(
                    ['id' => $id, 'version' => [$version, $version - 1]]
                )
                ->andReturn(
                    m::mock()
                    ->shouldReceive('fetchAll')
                    ->andReturn($results)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->shouldReceive('setHistoryTable')
                ->with($table)
                ->once()
                ->getMock()
            );

        $expected = [
            [
                'name' => 'foo',
                'oldValue' => 'bar1',
                'newValue' => 'bar2'
            ],
            [
                'name' => 'cake',
                'oldValue' => 'baz1',
                'newValue' => 'baz2'
            ]
        ];

        $this->assertEquals($expected, $this->sut->fetchEventHistoryDetails($id, $version, $table));
    }

    public function testFetchEventHistoryDetailsWithDeletedDate(): void
    {
        $table = 'application_hist';
        $id = 1;
        $version = 2;

        $results = [
            [ // new row
                'foo' => 'bar2',
                'cake' => 'baz2',
                'same' => 'value',
                'deleted_date' => '2026-01-28',
                'version' => 2
            ],
            [ // old row
                'foo' => 'bar1',
                'cake' => 'baz1',
                'same' => 'value',
                'deleted_date' => null,
                'version' => 1
            ]
        ];

        $mockQuery = m::mock()
            ->shouldReceive('setHistoryTable')->with($table)->once()->andReturnSelf()
            ->shouldReceive('execute')
            ->with(['id' => $id, 'version' => [$version, $version - 1]])
            ->andReturn(
                m::mock()
                ->shouldReceive('fetchAll')
                ->andReturn($results)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->dbQueryService->shouldReceive('get')
            ->with('EventHistory\GetEventHistoryDetails')
            ->andReturn($mockQuery);

        $expected = [
            ['name' => 'foo', 'oldValue' => 'bar1', 'newValue' => ''],
            ['name' => 'cake', 'oldValue' => 'baz1', 'newValue' => ''],
            ['name' => 'same', 'oldValue' => 'value', 'newValue' => ''],
            ['name' => 'deleted_date', 'oldValue' => '', 'newValue' => ''],
        ];

        $this->assertEquals(
            $expected,
            $this->sut->fetchEventHistoryDetails($id, $version, $table)
        );
    }



    public function testApplyListJoins(): void
    {
        $this->setUpSut(Repo::class, true);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('case')->once()->andReturnSelf()
            ->shouldReceive('with')->with('licence')->once()->andReturnSelf()
            ->shouldReceive('with')->with('application')->once()->andReturnSelf()
            ->shouldReceive('with')->with('organisation')->once()->andReturnSelf()
            ->shouldReceive('with')->with('transportManager')->once()->andReturnSelf()
            ->shouldReceive('with')->with('busReg')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpApplication')->once()->andReturnSelf();

        $this->assertNull($this->sut->applyListJoins($qb));
    }

    public function testFetchByTask(): void
    {
        $taskId = 1;

        $this->setUpSut(Repo::class, true);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('expr')
            ->andReturn(
                m::mock()
                ->shouldReceive('eq')
                ->with('m.task', ':task')
                ->andReturn('expr')
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('andWhere')
            ->with('expr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('task', $taskId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                ->shouldReceive('getResult')
                ->with(Query::HYDRATE_ARRAY)
                ->andReturn(['foo'])
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('eventHistoryType', 'eht')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('user', 'u')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('u.contactDetails', 'cd')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('cd.person', 'p')
            ->once()
            ->getMock();

        $this->assertEquals(['foo'], $this->sut->fetchByTask($taskId));
    }

    public function testFetchPreviousLicenceStatus(): void
    {
        $licenceId = 1;
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn(7);
        $this->mockCreateQueryBuilder($qb);

        $this->sut->fetchPreviousLicenceStatus($licenceId);

        $expectedQuery = 'QUERY SELECT eht.id INNER JOIN m.eventHistoryType eht INNER JOIN m.licence l AND eht.id IN [7,31,75] AND l.id = [[' . $licenceId . ']] ORDER BY m.eventDatetime DESC LIMIT 1';

        $this->assertSame($expectedQuery, $this->query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchPreviousLicenceStatusDataProvider')]
    public function testFetchPreviousLicenceStatusReturn(mixed $eventTypeId, mixed $expectedStatus): void
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($eventTypeId);
        $this->mockCreateQueryBuilder($qb);

        $result = $this->sut->fetchPreviousLicenceStatus(1);
        $expectedResult = ['status' => $expectedStatus];

        self::assertEquals($expectedResult, $result);
    }

    public static function fetchPreviousLicenceStatusDataProvider(): array
    {
        return [
            'case_curtailed' => [
                'eventTypeId' => 7,
                'expectedStatus' => Licence::LICENCE_STATUS_CURTAILED
            ],
            'case_suspended' => [
                'eventTypeId' => 31,
                'expectedStatus' => Licence::LICENCE_STATUS_SUSPENDED
            ],
            'case_valid' => [
                'eventTypeId' => 75,
                'expectedStatus' => Licence::LICENCE_STATUS_VALID
            ]
        ];
    }

    public function testFetchPreviousLicenceStatusNoResult(): void
    {
        $licenceId = 1;

        $qb = $this->createMockQb();
        $exception = new NoResultException();

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($qb)
            ->getMock();
        $qb->shouldReceive('getSingleScalarResult')
            ->once()
            ->andThrow($exception);

        $this->mockCreateQueryBuilder($qb);

        $this->assertSame(['status' => Licence::LICENCE_STATUS_VALID], $this->sut->fetchPreviousLicenceStatus($licenceId));
    }

    public function testFetchPreviousLicenceStatusException(): void
    {
        $licenceId = 1;

        $qb = $this->createMockQb();

        $ex = new \Exception('testException');
        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($qb)
            ->getMock();
        $qb->shouldReceive('getSingleScalarResult')
            ->once()
            ->andThrow($ex);

        $this->mockCreateQueryBuilder($qb);

        $this->expectExceptionMessage('testException');

        $this->sut->fetchPreviousLicenceStatus($licenceId);
    }
}
