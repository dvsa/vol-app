<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Mockery as m;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication::class)]
class TransportManagerApplicationTest extends RepositoryTestCase
{
    public const APP_ID = 9001;

    /** @var  Repository\TransportManagerApplication | m\MockInterface */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockQb;

    public function setUp(): void
    {
        $this->setUpSut(Repository\TransportManagerApplication::class, true);

        $this->mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
    }

    public function testFetchWithContactDetailsByApplication(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tma')->once()->andReturn($mockQb);

        $mockQb->shouldReceive('leftJoin')->with('tma.transportManager', 'tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('tma.tmApplicationStatus', 'tmas')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('tm.homeCd', 'hcd')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('hcd.person', 'hp')->once()->andReturnSelf();
        $mockQb->shouldReceive('select')->with('tma.id')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')->with('tma.action')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')->with('tm.id as tmid')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')
            ->with('tmas.id as tmasid, tmas.description as tmasdesc')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('addSelect')->with('hcd.emailAddress')->once()->andReturnSelf();
        $mockQb->shouldReceive('addSelect')
            ->with('hp.birthDate, hp.forename, hp.familyName')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('tma.application', ':applicationId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('applicationId', self::APP_ID)->once();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithContactDetailsByApplication(self::APP_ID));
    }

    public function testFetchDetails(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tma')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tma.application', 'a')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tma.otherLicences', 'ol')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ol.role')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.goodsOrPsv', 'gop')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.licence')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.status')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(self::APP_ID)->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('with')->with('tma.transportManager', 'tm')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tm.homeCd', 'hcd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('hcd.address', 'hadd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('hadd.countryCode')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('hcd.person', 'hp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tm.workCd', 'wcd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('wcd.address', 'wadd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('wadd.countryCode')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchDetails(self::APP_ID));
    }

    public function testFetchDetailsEmpty(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tma')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tma.application', 'a')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tma.otherLicences', 'ol')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ol.role')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.goodsOrPsv', 'gop')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.licence')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.status')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(self::APP_ID)->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('with')->with('tma.transportManager', 'tm')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tm.homeCd', 'hcd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('hcd.address', 'hadd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('hadd.countryCode')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('hcd.person', 'hp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tm.workCd', 'wcd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('wcd.address', 'wadd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('wadd.countryCode')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn([]);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->sut->fetchDetails(self::APP_ID);
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListJoins(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockQb = m::mock();

        $this->sut->shouldReceive('getQueryBuilder')->with()->once()->andReturn($mockQb);

        $mockQb->shouldReceive('with')->with('application', 'a')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('a.licence', 'l')->once()->andReturnSelf();

        $this->sut->applyListJoins($mockDqb);
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListFiltersUser(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('join')->with('tma.transportManager', 'tm')->once();
        $mockDqb->shouldReceive('join')->with('tm.users', 'u')->once();
        $mockDqb->shouldReceive('expr->eq')->with('u.id', ':user')->once()->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('user', 73)->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(['user' => 73]);
        $this->sut->applyListFilters($mockDqb, $query);
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListFiltersApplication(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->eq')->with('tma.application', ':application')->once()->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('application', 73)->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(['application' => 73]);
        $this->sut->applyListFilters($mockDqb, $query);
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListFiltersTransportManager(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->eq')->with('tma.transportManager', ':transportManager')->once()
            ->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('transportManager', 73)->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(['transportManager' => 73]);
        $this->sut->applyListFilters($mockDqb, $query);
    }

    /**
     * Mock SUT so that can just test the protected method
     */
    public function testApplyListFiltersAppStatuses(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->in')->with('a.status', ':appStatuses')->once()->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('appStatuses', ['st1', 'st2'])->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(
            ['appStatuses' => ['st1', 'st2']]
        );
        $this->sut->applyListFilters($mockDqb, $query);
    }

    public function testFetchForTransportManager(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tma')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tmType', 'tmt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('application', 'a')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.licence', 'al')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('al.organisation', 'alo')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.status', 'ast')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('transportManager', 'tm')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tmApplicationStatus', 'tmast')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('tma.transportManager', ':transportManager')->once()->andReturn('tm');
        $mockQb->shouldReceive('where')->with('tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('transportManager', 1)->once();

        $mockQb->shouldReceive('expr->neq')->with('tma.action', ':action')->once()->andReturn('ac');
        $mockQb->shouldReceive('andWhere')->with('ac')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('action', 'D')->once();

        $statuses = ['s0', 's1'];
        $mockQb->shouldReceive('expr->in')->with('a.status', $statuses)->once()->andReturn('IN_STATUS');
        $mockQb->shouldReceive('andWhere')->with('IN_STATUS')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertEquals(['RESULT'], $this->sut->fetchForTransportManager(1, $statuses));
    }

    public function testFetchByTmAndApplication(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tma')->once()->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('tma.transportManager', ':tmId')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('tmId', 1)->once();

        $mockQb->shouldReceive('expr->eq')->with('tma.application', ':applicationId')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('applicationId', 2)->once();

        $mockQb->shouldReceive('expr->neq')->with('tma.action', ':action')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('action', 'D')->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');
        $this->assertEquals('RESULT', $this->sut->fetchByTmAndApplication(1, 2, true));
    }

    public function testFetchForResponsibilities(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('tma')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('application', 'a')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tmType', 'tmty')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.licence', 'al')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('al.organisation', 'alo')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.status', 'ast')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('transportManager', 'tm')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tm.tmType', 'tmt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('tmApplicationStatus', 'tmast')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(1)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')->once()->andReturn(['RESULT']);
        $this->assertEquals(['RESULT'], $this->sut->fetchForResponsibilities(1));
    }

    public function testApplyListFiltersFilterOrgUser(): void
    {
        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('join')->with('tma.transportManager', 'tm')->once();
        $mockDqb->shouldReceive('join')->with('tm.users', 'u')->once();
        $mockDqb->shouldReceive('join')->with('l.organisation', 'o')->once();
        $mockDqb->shouldReceive('join')->with('o.organisationUsers', 'ou')->once();
        $mockDqb->shouldReceive('join')->with('ou.user', 'ouu')->once();
        $mockDqb->shouldReceive('expr->eq')->with('u.id', ':user')->once()->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('user', 73)->once();
        $mockDqb->shouldReceive('expr->eq')->with('ouu.id', ':orgUsersUser')->once()->andReturn('EXPR1');
        $mockDqb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('orgUsersUser', 73)->once();

        $query = \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(
            ['user' => 73, 'filterByOrgUser' => true]
        );
        $this->sut->applyListFilters($mockDqb, $query);
    }

    public function testFetchStatByAppId(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');

        $this->mockCreateQueryBuilder($mockQb);

        $mockQb->shouldReceive('getQuery->getOneOrNullResult')
            ->once()
            ->andReturn(
                [
                    TransportManagerApplication::ACTION_ADD => 3,
                    TransportManagerApplication::ACTION_UPDATE => 5,
                    TransportManagerApplication::ACTION_DELETE => 7,
                ]
            );

        $expectQry = '{QUERY}' .
            ' SELECT tma.id' .
            ' GROUP BY tma.application' .
            ' AND tma.application = [[' . self::APP_ID . ']]' .
            ' SELECT SUM(CASE WHEN tma.action = \'A\' THEN 1 ELSE 0 END) AS A' .
            ' SELECT SUM(CASE WHEN tma.action = \'U\' THEN 1 ELSE 0 END) AS U' .
            ' SELECT SUM(CASE WHEN tma.action = \'D\' THEN 1 ELSE 0 END) AS D';

        $actual = $this->sut->fetchStatByAppId(self::APP_ID);

        static::assertEquals($expectQry, $this->query);
        static::assertEquals(
            [
                'action' => [
                    'A' => 3,
                    'U' => 5,
                    'D' => 7,
                ]
            ],
            $actual
        );
    }
}
