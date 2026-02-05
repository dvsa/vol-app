<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\Query\Organisation\FixIsIrfo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Organisation\FixIsUnlicenced;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as Repo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Organisation::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class OrganisationTest extends RepositoryTestCase
{
    /**
     * @var Repo
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class, true);
    }

    public function testFetchBusinessDetailsByIdNotFound(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn(null);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111)
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('o')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchBusinessDetailsUsingId($command);
    }

    public function testFetchBusinessDetailsById(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn(
                [
                    [
                        'foo' => 'bar'
                    ]
                ]
            );

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111)
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('o')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $result = $this->sut->fetchBusinessDetailsUsingId($command);

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testFetchIrfoDetailsById(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn(
                [
                    [
                        'foo' => 'bar'
                    ]
                ]
            );

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('irfoNationality')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('irfoPartners')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('tradingNames', 'tn')
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once()
            ->with('irfoContactDetails')
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('o')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $result = $this->sut->fetchIrfoDetailsUsingId($command);

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testGetByCompanyOrLlpNo(): void
    {
        $companyNumber = '01234567';

        $licences1 = new ArrayCollection();
        $lic1 = m::mock(LicenceEntity::class)
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(LicenceEntity::LICENCE_STATUS_VALID)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $licences1->add($lic1);

        $licences2 = new ArrayCollection();
        $lic2 = m::mock(LicenceEntity::class)
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $licences2->add($lic2);

        $org1 = m::mock(Organisation::class)
            ->shouldReceive('getLicences')
            ->andReturn($licences1)
            ->once()
            ->getMock();

        $org2 = m::mock(Organisation::class)
            ->shouldReceive('getLicences')
            ->andReturn($licences2)
            ->once()
            ->getMock();

        $results = [$org1, $org2];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->with($qb)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licences')
            ->once()
            ->andReturnSelf();

        $where = m::mock();
        $qb->shouldReceive('expr->eq')
            ->with('o.companyOrLlpNo', ':companyNumber')
            ->andReturn($where);
        $qb
            ->shouldReceive('andWhere')
            ->with($where)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('companyNumber', $companyNumber)
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn($results);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->sut->getByCompanyOrLlpNo($companyNumber);
    }

    public function testGetByCompanyOrLlpNoNotFound(): void
    {
        $companyNumber = '01234567';

        $licences1 = new ArrayCollection();
        $lic1 = m::mock(LicenceEntity::class)
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();
        $licences1->add($lic1);

        $org1 = m::mock(Organisation::class)
            ->shouldReceive('getLicences')
            ->andReturn($licences1)
            ->once()
            ->getMock();

        $results = [$org1];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->with($qb)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licences')
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('expr->eq');
        $qb
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn($results);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->expectException(NotFoundException::class);

        $this->sut->getByCompanyOrLlpNo($companyNumber);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchByStatusPaginatedWithNullStatus(): void
    {
        $query = m::mock(CpidOrganisation::class)->makePartial();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->andReturnSelf();

        $qb->shouldReceive('where');
        $qb->shouldReceive('expr->isNull');

        $this->sut->shouldReceive('fetchPaginatedList');
        $this->sut->shouldReceive('fetchPaginatedCount');

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->sut->fetchByStatusPaginated($query);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchByStatusPaginatedWithStatus(): void
    {
        $query = m::mock(CpidOrganisation::class)
            ->makePartial()
            ->shouldReceive('getCpid')
            ->andReturn('op_cpid_central_government');

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->andReturnSelf();

        $this->sut->shouldReceive('getRefdataReference');

        $qb->shouldReceive('where');
        $qb->shouldReceive('expr->eq');

        $qb->shouldReceive('setParameter');

        $this->sut->shouldReceive('fetchPaginatedList');
        $this->sut->shouldReceive('fetchPaginatedCount');

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->sut->fetchByStatusPaginated($query->getMock());
    }

    public function testFetchAllByStatusForCpidExportWithStatus(): void
    {
        /** @var m\MockInterface $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->andReturnSelf();

        $qb->shouldReceive('where');
        $qb->shouldReceive('expr->eq');

        $qb->shouldReceive('setParameter');

        $qb->shouldReceive('select')
            ->with('o.id', 'o.name', 'r.id AS cpid');

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn(m::mock()->shouldReceive('iterate')->getMock());

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->sut->fetchAllByStatusForCpidExport('op_cpid_central_government');
    }

    public function testFetchAllByStatusForCpidExportWithNullStatus(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->andReturnSelf();

        $qb->shouldReceive('where');
        $qb->shouldReceive('expr->isNull');

        $qb->shouldReceive('select')
            ->with('o.id', 'o.name', 'r.id AS cpid');

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn(m::mock()->shouldReceive('iterate')->getMock());

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Organisation::class)
            ->andReturn($repo);

        $this->sut->fetchAllByStatusForCpidExport(null);
    }

    public function testFixIsIrfo(): void
    {
        $this->dbQueryService->shouldReceive('get')->with(FixIsIrfo::class)->once()->andReturn(
            m::mock()->shouldReceive('execute')->with()->once()->andReturn(
                m::mock()->shouldReceive('rowCount')->with()->once()->andReturn(52)->getMock()
            )->getMock()
        );

        $result = $this->sut->fixIsIrfo();

        $this->assertSame(52, $result);
    }

    public function testFixIsUnlicenced(): void
    {
        $this->dbQueryService->shouldReceive('get')->with(FixIsUnlicenced::class)->once()->andReturn(
            m::mock()->shouldReceive('execute')->with()->once()->andReturn(
                m::mock()->shouldReceive('rowCount')->with()->once()->andReturn(12)->getMock()
            )->getMock()
        );

        $result = $this->sut->fixIsUnlicenced();

        $this->assertSame(12, $result);
    }

    public function testGetAllOrganisationsForCompaniesHouse(): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULTS']);

        $this->sut->getAllOrganisationsForCompaniesHouse();

        $expectedQuery = '[QUERY] SELECT o.companyOrLlpNo DISTINCT INNER JOIN Dvsa\Olcs\Api\Entity\Licence\Licence l ' .
        'WITH l.organisation = o.id ' .
        'AND l.status IN [[["lsts_consideration","lsts_suspended","lsts_valid","lsts_curtailed","lsts_granted"]]] ' .
        'AND o.companyOrLlpNo IS NOT NULL AND o.type IN [[["org_t_rc","org_t_llp"]]]';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
