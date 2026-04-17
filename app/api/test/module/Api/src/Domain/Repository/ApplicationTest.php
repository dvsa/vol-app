<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\VersionConflictException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Application::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ApplicationTest extends RepositoryTestCase
{
    public const APP_ID = 8001;
    public const ORG_ID = 7001;

    /** @var Repository\Application | m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\Application::class);
    }

    public function testLockWithoutApplication(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->lock(m::mock(Licence::class), 1);
    }

    public function testLockWithConflict(): void
    {
        $entity = m::mock(Application::class);

        $this->em->shouldReceive('lock')
            ->with($entity, LockMode::OPTIMISTIC, 1)
            ->andThrow(OptimisticLockException::class);

        $this->expectException(VersionConflictException::class);

        $this->sut->lock($entity, 1);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testLock(): void
    {
        $entity = m::mock(Application::class);

        $this->em->shouldReceive('lock')
            ->with($entity, LockMode::OPTIMISTIC, 1);

        $this->sut->lock($entity, 1);
    }

    public function testSaveWithoutApplication(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->save(m::mock(Licence::class));
    }

    public function testSave(): void
    {
        $entity = m::mock(Application::class);

        $this->em->shouldReceive('persist')
            ->once()
            ->with($entity)
            ->shouldReceive('flush')
            ->once();

        $this->sut->save($entity);
    }

    public function testDeleteWithoutApplication(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->delete(m::mock(Licence::class));
    }

    public function testDelete(): void
    {
        $entity = m::mock(Application::class);

        $this->em->shouldReceive('remove')
            ->once()
            ->with($entity)
            ->shouldReceive('flush')
            ->once();

        $this->sut->delete($entity);
    }

    public function testGetRefdataReference(): void
    {
        $id = 'foo';

        $this->em->shouldReceive('getReference')
            ->with(RefData::class, $id)
            ->andReturn('blah');

        $this->assertEquals('blah', $this->sut->getRefdataReference($id));
    }

    public function testGetCategoryReference(): void
    {
        $id = 'foo';

        $this->em->shouldReceive('getReference')
            ->with(Category::class, $id)
            ->andReturn('blah');

        $this->assertEquals('blah', $this->sut->getCategoryReference($id));
    }

    public function testGetSubCategoryReference(): void
    {
        $id = 'foo';

        $this->em->shouldReceive('getReference')
            ->with(SubCategory::class, $id)
            ->andReturn('blah');

        $this->assertEquals('blah', $this->sut->getSubCategoryReference($id));
    }

    public function testGetReference(): void
    {
        $id = 'foo';

        $this->em->shouldReceive('getReference')
            ->with(RefData::class, $id)
            ->andReturn('blah');

        $this->assertEquals('blah', $this->sut->getReference(RefData::class, $id));
    }

    public function testFetchUsingId(): void
    {
        /** @var QueryInterface | m\MockInterface $command */
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
            ->shouldReceive('with')
            ->once()
            ->with('licence')
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('a')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchUsingIdWithResults(): void
    {
        /** @var QueryInterface | m\MockInterface $command */
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        $result = m::mock(Application::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('licence')
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('a')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchWithLicenceAndOc(): void
    {
        $applicationId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('getQuery->getSingleResult')
            ->andReturn('RESULT');

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l.operatingCentres', 'l_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_oc.operatingCentre', 'l_oc_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_oc_oc.address', 'l_oc_oc_a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('operatingCentres', 'a_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('a_oc.operatingCentre', 'a_oc_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('a_oc_oc.address', 'a_oc_oc_a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with("l.enforcementArea", "l_ea")
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($applicationId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo);

        $result = $this->sut->fetchWithLicenceAndOc($applicationId);
        $this->assertEquals('RESULT', $result);
    }

    public function testFetchActiveForOrganisation(): void
    {
        $organisationId = 123;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr->eq')
            ->with('l.organisation', ':organisationId');

        $qb->shouldReceive('expr->in')
            ->with('a.status', ['apsts_consideration', 'apsts_granted']);

        $qb
            ->shouldReceive('innerJoin')
            ->with('a.licence', 'l', 'WITH', m::any())
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('organisationId', $organisationId)
            ->shouldReceive('getQuery->execute')
            ->andReturn('RESULT');

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo);

        $result = $this->sut->fetchActiveForOrganisation($organisationId);

        $this->assertEquals('RESULT', $result);
    }

    public function testFetchWithLicence(): void
    {
        $applicationId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn(['RESULT']);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($applicationId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo);

        $result = $this->sut->fetchWithLicence($applicationId);
        $this->assertEquals('RESULT', $result);
    }

    public function testFetchWithLicenceAndOrg(): void
    {
        $applicationId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn(['RESULT']);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('l.organisation', 'l_org')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($applicationId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo);

        $result = $this->sut->fetchWithLicenceAndOrg($applicationId);
        $this->assertEquals('RESULT', $result);
    }

    public function testFetchByOrgAndStatusForActiveLicences(): void
    {
        $qb = $this->createMockQb('{{QUERY}}');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->execute')->andReturn('EXPECT');

        $actual = $this->sut->fetchByOrgAndStatusForActiveLicences(self::ORG_ID, ['unit1', 'unit2']);

        static::assertEquals('EXPECT', $actual);

        static::assertEquals(
            '{{QUERY}} ' .
            'INNER JOIN a.licence l WITH l.organisation = [[' . self::ORG_ID . ']] ' .
            'AND a.status IN ["unit1","unit2"] ' .
            'AND (' .
            'a.isVariation = 0 ' .
            'OR (' .
            'l.status IN ["lsts_valid","lsts_suspended","lsts_curtailed"] ' .
            'AND a.isVariation = 1 ' .
            'AND (' .
            'a.variationType IS NULL ' .
            'OR a.variationType != [[' . Application::VARIATION_TYPE_DIRECTOR_CHANGE . ']]' .
            ')' .
            ')' .
            ')',
            $this->query
        );
    }

    public function testFetchWithLicenceNotFound(): void
    {
        $applicationId = 1;

        $this->expectException(NotFoundException::class);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn([]);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($applicationId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Application::class)
            ->andReturn($repo);

        $result = $this->sut->fetchWithLicence($applicationId);
        $this->assertEquals('RESULT', $result);
    }

    public function testApplyListJoins(): void
    {
        $this->setUpSut(Repository\Application::class, true);

        $this->sut->shouldReceive('getQueryBuilder')->with()->once()->andReturnSelf();
        $this->sut->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();

        /** @var QueryBuilder | m\MockInterface $mockQb */
        $mockQb = m::mock(QueryBuilder::class);
        $this->sut->applyListJoins($mockQb);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repository\Application::class, true);

        $status = 'unit_status';
        $orgId = 999;

        /** @var QueryBuilder | m\MockInterface $mockQb */
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('l.organisation', ':organisation')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('setParameter')->with('organisation', $orgId)->once();
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();

        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('a.status', ':STATUS')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('setParameter')->with('STATUS', $status)->once();

        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->orX')->with('EXPR4', 'EXPR5')->andReturn('EXPR3');
        $mockQb->shouldReceive('expr->eq')->with('a.isVariation', ':isVariation')->once()->andReturn('EXPR4');
        $mockQb->shouldReceive('expr->neq')->with('COALESCE(IDENTITY(a.variationType), \'\')', ':variationType')->once()->andReturn('EXPR5');
        $mockQb->shouldReceive('setParameter')->with('isVariation', false)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('variationType', Application::VARIATION_TYPE_DIRECTOR_CHANGE);

        $mockQuery = TransferQry\Application\GetList::create(
            [
                'organisation' => $orgId,
                'status' => $status,
            ]
        );

        $this->sut->applyListFilters($mockQb, $mockQuery);
    }

    public function testFetchWithTmLicences(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('a')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.tmLicences', 'ltml')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(1)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')->once()->andReturn(['RESULT']);
        $this->assertEquals(['RESULT'], $this->sut->fetchWithTmLicences(1));
    }

    public function testFetchForNtu(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('a')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.trafficArea', 'lta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('fees', 'f')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('f.feeType', 'ft')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('f.feeStatus', 'fs')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('a.status', ':appStatus')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('setParameter')
            ->with('appStatus', Application::APPLICATION_STATUS_GRANTED)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('f.feeStatus', ':feeStatus')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('setParameter')
            ->with('feeStatus', FeeEntity::STATUS_OUTSTANDING)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('ft.feeType', ':feeType')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('setParameter')
            ->with('feeType', [FeeTypeEntity::FEE_TYPE_GRANT, FeeTypeEntity::FEE_TYPE_GRANTVAR])->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);
        $this->assertEquals(['RESULT'], $this->sut->fetchForNtu());
    }

    public function testFetchAbandonedVariations(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('a')->once()->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('a.isVariation', ':isVariation')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('setParameter')->with('isVariation', 1)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('a.variationType', ':variationType')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('setParameter')
            ->with('variationType', Application::VARIATION_TYPE_DIRECTOR_CHANGE)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('a.status', ':status')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('setParameter')
            ->with('status', Application::APPLICATION_STATUS_NOT_SUBMITTED)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();

        $olderThanDate = date('Y-m-d H:i:s', strtotime('- 4 hours'));
        $mockQb->shouldReceive('expr->lt')->with('a.createdOn', ':olderThanDate')->once()->andReturn('EXPR4');
        $mockQb->shouldReceive('setParameter')->with('olderThanDate', $olderThanDate)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR4')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertEquals(['RESULT'], $this->sut->fetchAbandonedVariations($olderThanDate));
    }

    public function testFetchOpenApplicationsForLicence(): void
    {
        $licenceId = 5;
        $status = Application::APPLICATION_STATUS_UNDER_CONSIDERATION;

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $result = $this->sut->fetchOpenApplicationsForLicence($licenceId);

        $this->assertEquals('QUERY AND a.licence = [[5]] AND a.status = ' . "[[$status]]", $this->query);

        $this->assertEquals(['RESULT'], $result);
    }
}
