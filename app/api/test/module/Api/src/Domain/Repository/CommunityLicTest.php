<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Hamcrest\Core\IsEqual;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Community Lic test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CommunityLicTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(CommunityLicRepo::class);
    }

    public function testFetchOfficeCopy(): void
    {
        $licenceId = 1;
        $issueNo = 0;
        $mockQb = m::mock(QueryBuilder::class);
        $licenceExpr = $this->mockExprEq('m.licence', ':licence');
        $issueNoExpr = $this->mockExprEq('m.issueNo', ':issueNo');
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn($licenceExpr);
        $mockQb->shouldReceive('andWhere')->with($licenceExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('m.issueNo', ':issueNo')->once()->andReturn($issueNoExpr);
        $mockQb->shouldReceive('andWhere')->with($issueNoExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('issueNo', $issueNo)->once()->andReturnSelf();

        $pendingExpr = $this->mockExprEq('m.status', ':pending');
        $activeExpr = $this->mockExprEq('m.status', ':active');
        $withdrawnExpr = $this->mockExprEq('m.status', ':withdrawn');
        $suspendedExpr = $this->mockExprEq('m.status', ':suspended');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':pending')->once()->andReturn($pendingExpr);
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':active')->once()->andReturn($activeExpr);
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':withdrawn')->once()->andReturn($withdrawnExpr);
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':suspended')->once()->andReturn($suspendedExpr);
        $mockQb->shouldReceive('setParameter')
            ->with('pending', CommunityLicEntity::STATUS_PENDING)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('active', CommunityLicEntity::STATUS_ACTIVE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('withdrawn', CommunityLicEntity::STATUS_WITHDRAWN)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('suspended', CommunityLicEntity::STATUS_SUSPENDED)
            ->once()
            ->andReturnSelf();
        $statusesExpr = $this->mockOrX();
        $mockQb->shouldReceive('expr->orX')
            ->with($pendingExpr, $activeExpr, $withdrawnExpr, $suspendedExpr)
            ->once()
            ->andReturn($statusesExpr);
        $mockQb->shouldReceive('andWhere')->with($statusesExpr)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $query = m::mock(\Doctrine\ORM\Query::class);
        $mockQb->shouldReceive('getQuery')->once()->andReturn($query);
        $query->shouldReceive('getOneOrNullResult')
            ->once()
            ->andReturn(['result']);

        $this->assertEquals(['result'], $this->sut->fetchOfficeCopy($licenceId));
    }

    public function testFetchValidLicences(): void
    {
        $licenceId = 1;
        $issueNo = 0;
        $mockQb = m::mock(QueryBuilder::class);
        $licenceExpr = $this->mockExprEq('m.licence', ':licence');
        $issueNoExpr = $this->mockExprNeq('m.issueNo', ':issueNo');
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn($licenceExpr);
        $mockQb->shouldReceive('andWhere')->with($licenceExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->neq')->with('m.issueNo', ':issueNo')->once()->andReturn($issueNoExpr);
        $mockQb->shouldReceive('andWhere')->with($issueNoExpr)->once()->andReturnSelf();

        $pendingExpr = $this->mockExprEq('m.status', ':pending');
        $activeExpr = $this->mockExprEq('m.status', ':active');
        $withdrawnExpr = $this->mockExprEq('m.status', ':withdrawn');
        $suspendedExpr = $this->mockExprEq('m.status', ':suspended');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':pending')->once()->andReturn($pendingExpr);
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':active')->once()->andReturn($activeExpr);
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':withdrawn')->once()->andReturn($withdrawnExpr);
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':suspended')->once()->andReturn($suspendedExpr);
        $statusesExpr = $this->mockOrX();
        $mockQb->shouldReceive('expr->orX')
            ->with(
                $pendingExpr,
                $activeExpr,
                $withdrawnExpr,
                $suspendedExpr
            )
            ->once()
            ->andReturn($statusesExpr);
        $mockQb->shouldReceive('andWhere')->with($statusesExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('issueNo', $issueNo)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('pending', CommunityLicEntity::STATUS_PENDING)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('active', CommunityLicEntity::STATUS_ACTIVE)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('withdrawn', CommunityLicEntity::STATUS_WITHDRAWN)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('suspended', CommunityLicEntity::STATUS_SUSPENDED)->once()->andReturnSelf();
        $mockQb->shouldReceive('orderBy')->with('m.issueNo', 'ASC')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $query = m::mock(\Doctrine\ORM\Query::class);
        $mockQb->shouldReceive('getQuery')->once()->andReturn($query);
        $query->shouldReceive('execute')->once()->andReturn('result');
        $this->assertEquals('result', $this->sut->fetchValidLicences($licenceId));
    }

    public function testFetchLicencesById(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $idExpr = $this->mockExprIn('m.id', ':ids');
        $mockQb->shouldReceive('expr->in')->with('m.id', ':ids')->once()->andReturn($idExpr);
        $mockQb->shouldReceive('andWhere')->with($idExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('ids', [1])->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $query = m::mock(\Doctrine\ORM\Query::class);
        $mockQb->shouldReceive('getQuery')->once()->andReturn($query);
        $query->shouldReceive('execute')->once()->andReturn('result');
        $this->assertEquals('result', $this->sut->fetchLicencesByIds([1]));
    }

    public function testApplyListFilters(): void
    {
        // it's quite hard to test this protected method because of a lot of doctrine's
        // internal methods mocking required
        // so it's more reasonable to test this method in isolation
        $sut = m::mock(CommunityLicRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $licenceId = 1;
        $statuses = 'active';
        $conditions = [
            'm.status = :status0'
        ];

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('getStatuses')
            ->andReturn($statuses)
            ->twice()
            ->shouldReceive('getLicence')
            ->andReturn($licenceId)
            ->twice()
            ->getMock();

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->orX->addMultiple')->with($conditions)->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status0', 'active')->once()->andReturnSelf();
        $licenceExpr = $this->mockExprEq('m.licence', ':licence');
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn($licenceExpr);
        $mockQb->shouldReceive('andWhere')->with($licenceExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $mockQuery);
    }

    public function testExpireAllForLicence(): void
    {
        $licenceId = 123;

        $this->expectQueryWithData('CommunityLicence\ExpireAllForLicence', ['licence' => 123, 'status' => 'foo']);

        $this->sut->expireAllForLicence($licenceId, 'foo');
    }

    public function testExpireAllForLicenceNoStatus(): void
    {
        $licenceId = 123;

        $this->expectQueryWithData('CommunityLicence\ExpireAllForLicence', ['licence' => 123]);

        $this->sut->expireAllForLicence($licenceId);
    }

    public function testFetchForSuspension(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('innerJoin')->with('m.communityLicSuspensions', 's')->andReturnSelf();
        $mockQb->shouldReceive('innerJoin')->with('s.communityLicSuspensionReasons', 'sr')->andReturnSelf();

        $statusExpr = $this->mockExprEq('m.status', ':status');
        $startDateExpr = $this->mockExprLte('s.startDate', ':startDate');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':status')->once()->andReturn($statusExpr);
        $mockQb->shouldReceive('expr->lte')->with('s.startDate', ':startDate')->once()->andReturn($startDateExpr);
        $mockQb->shouldReceive('andWhere')->with($statusExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with($startDateExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', CommunityLicEntity::STATUS_ACTIVE)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('startDate', 'foo')->andReturnSelf();
        $endDateGtExpr = $this->mockExprGt('s.endDate', ':endDate');
        $mockQb->shouldReceive('expr->gt')->with('s.endDate', ':endDate')->once()->andReturn($endDateGtExpr);
        $mockQb->shouldReceive('expr->isNull')->with('s.endDate')->once()->andReturn('endDateNull');
        $orExpr = $this->mockOrX();
        $mockQb->shouldReceive('expr->orX')->with('endDateNull', $endDateGtExpr)->once()->andReturn($orExpr);
        $mockQb->shouldReceive('andWhere')->with($orExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('endDate', 'foo')->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $query = m::mock(\Doctrine\ORM\Query::class);
        $mockQb->shouldReceive('getQuery')->once()->andReturn($query);
        $query->shouldReceive('execute')->once()->andReturn('result');
        $this->assertEquals('result', $this->sut->fetchForSuspension('foo'));
    }

    public function testFetchForActivation(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('innerJoin')->with('m.communityLicSuspensions', 's')->andReturnSelf();
        $mockQb->shouldReceive('innerJoin')->with('s.communityLicSuspensionReasons', 'sr')->andReturnSelf();

        $statusExpr = $this->mockExprEq('m.status', ':status');
        $endDateExpr = $this->mockExprLte('s.endDate', ':endDate');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':status')->once()->andReturn($statusExpr);
        $mockQb->shouldReceive('expr->lte')->with('s.endDate', ':endDate')->once()->andReturn($endDateExpr);
        $mockQb->shouldReceive('andWhere')->with($statusExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with($endDateExpr)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', CommunityLicEntity::STATUS_SUSPENDED)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('endDate', 'foo')->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $query = m::mock(\Doctrine\ORM\Query::class);
        $mockQb->shouldReceive('getQuery')->once()->andReturn($query);
        $query->shouldReceive('execute')->once()->andReturn('result');
        $this->assertEquals('result', $this->sut->fetchForActivation('foo'));
    }

    public function testCountActiveByLicenceIdIsDefined(): void
    {
        $this->assertIsCallable([$this->sut, 'countActiveByLicenceId']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdFiltersResultsByLicenceId(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);
        $expectedCondition = new Comparison('m.licence', Comparison::EQ, ':licence');

        // Define Expectations
        $queryBuilder->shouldReceive('andWhere')->once()->with(IsEqual::equalTo($expectedCondition));

        // Execute
        $sut->countActiveByLicenceId(1);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdSetsALicenceIdParameterWithTheProvidedLicenceIdValue(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);
        $expectedLicenceId = 8;

        $queryBuilder->shouldReceive('setParameters')
            ->once()
            ->with(m::type(ArrayCollection::class));

        // Execute
        $sut->countActiveByLicenceId($expectedLicenceId);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdFiltersResultsToCommunityLicencesByStatus(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);
        $expectedCondition = new Comparison('m.status', Comparison::EQ, ':status');

        // Define Expectations
        $queryBuilder->shouldReceive('andWhere')->once()->with(IsEqual::equalTo($expectedCondition));

        // Execute
        $sut->countActiveByLicenceId(1);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdFiltersResultsToCommunityLicencesThatAreActive(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);

        $queryBuilder->shouldReceive('setParameters')
            ->once()
            ->with(m::type(ArrayCollection::class));

        // Execute
        $sut->countActiveByLicenceId(1);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdFiltersResultsByIssueNumber(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);
        $expectedCondition = new Comparison('m.issueNo', Comparison::NEQ, ':issueNo');

        // Define Expectations
        $queryBuilder->shouldReceive('andWhere')->once()->with(IsEqual::equalTo($expectedCondition));

        // Execute
        $sut->countActiveByLicenceId(1);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdFiltersResultsToCommunityLicencesWithAZeroIssueNumber(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);

        $queryBuilder->shouldReceive('setParameters')
            ->once()
            ->with(m::type(ArrayCollection::class));

        // Execute
        $sut->countActiveByLicenceId(1);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdCountsCommunityLicenceIds(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);
        $expectedExpression = new Func('COUNT', ['m.id']);

        // Define Expectations
        $queryBuilder->shouldReceive('select')->once()->with(IsEqual::equalTo($expectedExpression));

        // Execute
        $sut->countActiveByLicenceId(1);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testCountActiveByLicenceIdIsDefined')]
    public function testCountActiveByLicenceIdReturnsTheIntegerFromTheExecutedQueryResult(): void
    {
        // Set Up
        $serviceManager = $this->setUpServiceManager();
        $queryBuilder = $this->resolveMockService($serviceManager, QueryBuilder::class);
        $sut = $this->setUpRepository($serviceManager, CommunityLicRepo::class);
        $query = m::mock(\Doctrine\ORM\Query::class)->shouldIgnoreMissing();
        $query->shouldReceive('getSingleScalarResult')->andReturn($expectedCount = 997);
        $queryBuilder->shouldReceive('getQuery')->andReturn($query);

        // Execute
        $result = $sut->countActiveByLicenceId(1);

        // Assert
        $this->assertEquals($expectedCount, $result);
    }
}
