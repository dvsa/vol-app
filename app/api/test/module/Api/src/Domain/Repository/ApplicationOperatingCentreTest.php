<?php

declare(strict_types=1);

/**
 * ApplicationOperatingCentreTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre as Repo;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as Qry;
use Doctrine\ORM\Query\Expr\Join;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;

/**
 * ApplicationOperatingCentreTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ApplicationOperatingCentreTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByApplication(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('aoc')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('operatingCentre', 'oc')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('oc.address')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('aoc.application', ':applicationId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('applicationId', 12)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByApplication(12));
    }

    public function testFetchByS4(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('aoc')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('aoc.s4', ':s4Id')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('s4Id', 12)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByS4(12));
    }

    public function testFetchByApplicationIdForOperatingCentres(): void
    {
        $mockRepoServiceManager = m::mock(\Dvsa\Olcs\Api\Domain\RepositoryServiceManager::class)
            ->shouldReceive('get')
            ->with('LicenceOperatingCentre')
            ->andReturn(
                m::mock()
                    ->shouldReceive('maybeRemoveAdrColumn')
                    ->andReturn(['foo' => 'bar'])
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $this->sut->initService($mockRepoServiceManager);

        $qb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getArrayResult')
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchByApplicationIdForOperatingCentres(111));

        $expected = implode(
            ' ',
            [
                '{QUERY}',
                'LEFT JOIN aoc.s4 s4',
                'INNER JOIN aoc.operatingCentre oc',
                'INNER JOIN oc.address oca',
                'LEFT JOIN oca.countryCode ocac',
                'LEFT JOIN oc.complaints occ WITH occ.status = [[ecst_open]]',
                'AND aoc.application = [[111]]',
                'SELECT s4',
                'SELECT oc',
                'SELECT oca',
                'SELECT ocac',
                'SELECT occ',
                'ORDER BY oca.id ASC'
            ]
        );

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchByApplicationIdForOperatingCentresWithQuery(): void
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $qb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = Qry::create(['sort' => 'adr', 'order' => 'ASC']);

        $sut->shouldReceive('createQueryBuilder')->with()->once()->andReturn($qb);

        $qb->shouldReceive('leftJoin')->with('aoc.s4', 's4')->once();
        $qb->shouldReceive('innerJoin')->with('aoc.operatingCentre', 'oc')->once();
        $qb->shouldReceive('innerJoin')->with('oc.address', 'oca')->once();
        $qb->shouldReceive('leftJoin')->with('oca.countryCode', 'ocac')->once();
        $qb->shouldReceive('expr->eq')->with('occ.status', ':complaintStatus')->andReturn('exp1')->once();
        $qb->shouldReceive('leftJoin')->with('oc.complaints', 'occ', Join::WITH, 'exp1')->once();
        $qb->shouldReceive('setParameter')->with('complaintStatus', Complaint::COMPLAIN_STATUS_OPEN)->once();
        $qb->shouldReceive('expr->eq')->with('aoc.application', ':application')->andReturn('exp2')->once();
        $qb->shouldReceive('andWhere')->with('exp2')->once();
        $qb->shouldReceive('setParameter')->with('application', 1)->once();
        $qb->shouldReceive('addSelect')->with('s4')->once();
        $qb->shouldReceive('addSelect')->with('oc')->once();
        $qb->shouldReceive('addSelect')->with('oca')->once();
        $qb->shouldReceive('addSelect')->with('ocac')->once();
        $qb->shouldReceive('addSelect')->with('occ')->once();
        $qb->shouldReceive('addSelect')
            ->with(
                "concat(ifnull(oca.addressLine1,''),ifnull(oca.addressLine2,''),ifnull(oca.addressLine3,''),"
                . "ifnull(oca.addressLine4,''),ifnull(oca.town,'')) as adr"
            )
            ->once();
        $sut->shouldReceive('buildDefaultListQuery')
            ->with($qb, $query, ['adr'])
            ->once()
            ->getMock();

        $qb->shouldReceive('getQuery->getArrayResult')
            ->andReturn(['foo' => 'bar']);

        $mockRepoServiceManager = m::mock(\Dvsa\Olcs\Api\Domain\RepositoryServiceManager::class)
            ->shouldReceive('get')
            ->with('LicenceOperatingCentre')
            ->andReturn(
                m::mock()
                    ->shouldReceive('maybeRemoveAdrColumn')
                    ->andReturn(['foo' => 'bar'])
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $sut->initService($mockRepoServiceManager);

        $this->assertEquals(['foo' => 'bar'], $sut->fetchByApplicationIdForOperatingCentres(1, $query));
    }

    public function testFindCorrespondingLoc(): void
    {
        /** @var Entity\OperatingCentre\OperatingCentre $oc */
        $oc = m::mock(Entity\OperatingCentre\OperatingCentre::class)->makePartial();

        /** @var Entity\Application\ApplicationOperatingCentre $aoc */
        $aoc = m::mock(Entity\Application\ApplicationOperatingCentre::class)->makePartial();
        $aoc->setOperatingCentre($oc);

        /** @var Entity\Licence\LicenceOperatingCentre $loc */
        $loc = m::mock(Entity\Licence\LicenceOperatingCentre::class)->makePartial();
        $loc->setOperatingCentre($oc);

        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var Entity\Licence\Licence $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        $foundLoc = $this->sut->findCorrespondingLoc($aoc, $licence);

        $this->assertSame($loc, $foundLoc);
    }

    public function testFindCorrespondingLocWithoutMatch(): void
    {
        /** @var Entity\OperatingCentre\OperatingCentre $oc */
        $oc = m::mock(Entity\OperatingCentre\OperatingCentre::class)->makePartial();

        /** @var Entity\Application\ApplicationOperatingCentre $aoc */
        $aoc = m::mock(Entity\Application\ApplicationOperatingCentre::class)->makePartial();
        $aoc->setOperatingCentre($oc);

        $locs = new ArrayCollection();

        /** @var Entity\Licence\Licence $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        $this->assertNull($this->sut->findCorrespondingLoc($aoc, $licence));
    }

    public function testFetchByApplicationOrderByAddress(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('aoc')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('operatingCentre', 'oc')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('oc.address', 'address')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('aoc.application', ':applicationId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('applicationId', 12)->once();
        $mockQb->shouldReceive('orderBy')->with('address.town')->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByApplicationOrderByAddress(12));
    }
}
