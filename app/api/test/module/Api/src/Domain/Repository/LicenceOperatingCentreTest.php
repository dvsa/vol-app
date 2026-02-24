<?php

declare(strict_types=1);

/**
 * LicenceOperatingCentreTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre as Repo;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Licence\OperatingCentres as Qry;
use Doctrine\ORM\Query\Expr\Join;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;

/**
 * LicenceOperatingCentreTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceOperatingCentreTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByLicence(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('loc')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('operatingCentre', 'oc')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('oc.address')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('loc.licence', ':licenceId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licenceId', 7634)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByLicence(7634));
    }

    public function testFetchByLicenceIdForOperatingCentres(): void
    {
        $qb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getArrayResult')
            ->andReturn([['foo']]);

        $this->assertEquals([['foo']], $this->sut->fetchByLicenceIdForOperatingCentres(111));

        $expected = implode(
            ' ',
            [
                '{QUERY}',
                'LEFT JOIN loc.s4 s4',
                'INNER JOIN loc.operatingCentre oc',
                'INNER JOIN oc.address oca',
                'LEFT JOIN oca.countryCode ocac',
                'LEFT JOIN oc.complaints occ WITH occ.status = [[ecst_open]]',
                'AND loc.licence = [[111]]',
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

    public function testFetchByLicenceIdForOperatingCentresWithQuery(): void
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $qb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = Qry::create(['sort' => 'adr', 'order' => 'ASC']);

        $sut->shouldReceive('createQueryBuilder')->with()->once()->andReturn($qb);

        $qb->shouldReceive('leftJoin')->with('loc.s4', 's4')->once();
        $qb->shouldReceive('innerJoin')->with('loc.operatingCentre', 'oc')->once();
        $qb->shouldReceive('innerJoin')->with('oc.address', 'oca')->once();
        $qb->shouldReceive('leftJoin')->with('oca.countryCode', 'ocac')->once();
        $qb->shouldReceive('expr->eq')->with('occ.status', ':complaintStatus')->andReturn('exp1')->once();
        $qb->shouldReceive('leftJoin')->with('oc.complaints', 'occ', Join::WITH, 'exp1')->once();
        $qb->shouldReceive('setParameter')->with('complaintStatus', Complaint::COMPLAIN_STATUS_OPEN)->once();
        $qb->shouldReceive('expr->eq')->with('loc.licence', ':licence')->andReturn('exp2')->once();
        $qb->shouldReceive('andWhere')->with('exp2')->once();
        $qb->shouldReceive('setParameter')->with('licence', 1)->once();
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

        $sut->shouldReceive('maybeRemoveAdrColumn')
            ->with(['foo' => 'bar'])
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->assertEquals(['foo' => 'bar'], $sut->fetchByLicenceIdForOperatingCentres(1, $query));
    }

    public function testMaybeRemoveAdrColumn(): void
    {
        $data = [
            [
                0 => [
                    'operatingCentre' => 'foo'
                ],
                'adr' => 'bar'
            ],
            [
                0 => [
                    'operatingCentre' => 'cake'
                ],
                'adr' => 'baz'
            ],
            [
                'operatingCentre' => 'baz'
            ]
        ];
        $expected = [
            [
                'operatingCentre' => 'foo'
            ],
            [
                'operatingCentre' => 'cake'
            ],
            [
                'operatingCentre' => 'baz'
            ],
        ];

        $this->assertEquals($expected, $this->sut->maybeRemoveAdrColumn($data));
    }
}
