<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Mockery as m;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence::class)]
class TransportManagerLicenceTest extends RepositoryTestCase
{
    /** @var  Repository\TransportManagerLicence */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\TransportManagerLicence::class, true);
    }

    public function testFetchWithContactDetailsByLicence(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tml')->once()->andReturn($mockQb);

        $mockQb->shouldReceive('join')->with('tml.transportManager', 'tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('join')->with('tm.homeCd', 'hcd')->once()->andReturnSelf();
        $mockQb->shouldReceive('join')->with('hcd.person', 'p')->once()->andReturnSelf();
        $mockQb->shouldReceive('select')->with('tml.id')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')->with('tm.id as tmid')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')->with('p.birthDate, p.forename, p.familyName')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')->with('hcd.emailAddress')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('tml.licence', ':licenceId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licenceId', 834)->once();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithContactDetailsByLicence(834));
    }

    public function testFetchRemovedTmForLicence(): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);

        $this->em->shouldReceive('getFilters->isEnabled')->with('soft-deleteable')->andReturn(false);
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULTS']);

        $licenceId = 1;
        $this->sut->fetchRemovedTmForLicence($licenceId);

        $expectedQuery = '[QUERY] AND tml.licence = [[' . $licenceId . ']] AND tml.deletedDate IS NOT NULL AND tml.lastTmLetterDate IS NULL';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForTransportManager(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tml')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tmType', 'tmt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.organisation', 'lo')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.status', 'ls')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('transportManager', 'tm')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('tml.transportManager', ':transportManager')->once()->andReturn('tm');
        $mockQb->shouldReceive('where')->with('tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('transportManager', 1)->once();

        $statuses = ['s0', 's1'];
        $mockQb->shouldReceive('expr->in')->with('l.status', $statuses)->once()->andReturn('IN_STATUS');
        $mockQb->shouldReceive('andWhere')->with('IN_STATUS')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertEquals(['RESULT'], $this->sut->fetchForTransportManager(1, $statuses));
    }

    public function testFetchForResponsibilities(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tml')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.organisation', 'lo')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.status', 'lst')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('transportManager', 'tm')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tm.tmType', 'tmty')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tmType', 'tmt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(1)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')->once()->andReturn(['RESULT']);
        $this->assertEquals(['RESULT'], $this->sut->fetchForResponsibilities(1));
    }

    public function testFetchByTmAndLicence(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tml')->once()->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('tml.transportManager', ':tmId')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('tmId', 1)->once();

        $mockQb->shouldReceive('expr->eq')->with('tml.licence', ':licenceId')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licenceId', 2)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');
        $this->assertEquals('RESULT', $this->sut->fetchByTmAndLicence(1, 2));
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListFiltersLicence(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->eq')->with('tml.licence', ':licence')->once()
            ->andReturn('EXPR');
        $mockDqb->shouldReceive('where')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('licence', 73)->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetList::create(['licence' => 73]);
        $this->sut->applyListFilters($mockDqb, $query);
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListFiltersTransportManager(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->eq')->with('tml.transportManager', ':transportManager')->once()
            ->andReturn('EXPR');
        $mockDqb->shouldReceive('where')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('transportManager', 73)->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetList::create(['transportManager' => 73]);
        $this->sut->applyListFilters($mockDqb, $query);
    }

    public function testFetchByLicence(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tml')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.applications', 'la')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('la.licenceType')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('transportManager', 'tm')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('tml.licence', ':licence')->once()->andReturn('cond1');
        $mockQb->shouldReceive('where')->with('cond1')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licence', 7)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertEquals(['RESULT'], $this->sut->fetchByLicence(7));
    }
}
