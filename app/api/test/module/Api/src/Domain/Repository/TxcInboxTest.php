<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Query\Bus\TxcInboxList;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as Repo;
use Mockery as m;

/**
 * TxcInboxTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TxcInboxTest extends RepositoryTestCase
{
    /** @var  Repo */
    protected $sut;

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

    public function testFetchListForOrganisationByBusReg(): void
    {
        $busRegId = 8888;
        $orgId = 7777;

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('busReg', 'b')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForOrganisationByBusReg($busRegId, $orgId));

        $this->assertEquals(
            'BLAH ' .
            'AND b.id = [[' . $busRegId . ']] ' .
            'AND m.localAuthority IS NULL ' .
            'AND m.organisation = [[' . $orgId . ']]',
            $this->query
        );
    }

    public function testFetchListForLocalAuthorityByBusReg(): void
    {
        $busRegId = 8888;

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('busReg', 'b')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForLocalAuthorityByBusReg($busRegId, 4));

        $this->assertEquals(
            'BLAH ' .
            'AND b.id = [[' . $busRegId . ']] ' .
            'AND m.fileRead = 0 ' .
            'AND m.localAuthority = [[4]]',
            $this->query
        );
    }

    public function testFetchListForLocalAuthorityByBusRegOperator(): void
    {
        $busRegId = 8888;

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('busReg', 'b')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForLocalAuthorityByBusReg($busRegId, null));

        $this->assertEquals(
            'BLAH ' .
            'AND b.id = [[' . $busRegId . ']] ' .
            'AND m.localAuthority IS NULL',
            $this->query
        );
    }

    public function testBuildDefaultQuery(): void
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockQi = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);

        $sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);

        $mockQb->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $mockQb->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('m.busReg', 'b')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('b.ebsrSubmissions', 'e')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('b.licence', 'l')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('b.otherServices')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('l.organisation')->once()->andReturnSelf();

        $sut->buildDefaultListQuery($mockQb, $mockQi);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);

        // organisation clause
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->with('m.localAuthority', ':localAuthority')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('localAuthority', 3)
            ->andReturnSelf();

        // status clause
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->with('b.status', ':status')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('status', 'foo')
            ->andReturnSelf();

        // subType clause
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->with('e.ebsrSubmissionType', ':ebsrSubmissionType')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('ebsrSubmissionType', 'bar')
            ->andReturnSelf();

        // fileRead clause
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->with('m.fileRead', '0')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf();

        $query = TxcInboxList::create(['localAuthority' => 3, 'subType' => 'bar', 'status' => 'foo']);

        $this->sut->applyListFilters($mockQb, $query);
    }

    public function testFetchLinkedToDocument(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('where')->with('m.zipDocument = :documentId')->andReturnSelf();
        $qb->shouldReceive('where')->with('m.pdfDocument = :documentId')->andReturnSelf();
        $qb->shouldReceive('where')->with('m.routeDocument = :documentId')->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchLinkedToDocument(23));

        $expectedQuery = 'BLAH OR m.zipDocument = [[23]] OR m.routeDocument = [[23]] OR m.pdfDocument = [[23]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByIdsForLocalAuthority(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByIdsForLocalAuthority([2], 4));

        $expectedQuery = 'BLAH AND m.localAuthority = [[4]] AND m.id IN [2]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByIdsForLocalAuthorityWithEmptyData(): void
    {
        $this->assertEquals([], $this->sut->fetchByIdsForLocalAuthority([], 4));
    }
}
