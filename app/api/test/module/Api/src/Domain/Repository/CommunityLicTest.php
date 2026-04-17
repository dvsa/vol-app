<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\IsEqual;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Community Lic test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(CommunityLicRepo::class);
    }

    public function testFetchOfficeCopy(): void
    {
        $licenceId = 1;
        $issueNo = 0;
        $mockQb = m::mock();
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn('foo');
        $mockQb->shouldReceive('andWhere')->with('foo')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('m.issueNo', ':issueNo')->once()->andReturn('bar');
        $mockQb->shouldReceive('andWhere')->with('bar')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('issueNo', $issueNo)->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.status', ':pending')->once()->andReturn('pending');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':active')->once()->andReturn('active');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':withdrawn')->once()->andReturn('withdrawn');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':suspended')->once()->andReturn('suspended');
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
        $mockQb->shouldReceive('expr->orX')
            ->with('pending', 'active', 'withdrawn', 'suspended')
            ->once()
            ->andReturn('statuses');
        $mockQb->shouldReceive('andWhere')->with('statuses')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getOneOrNullResult')
            ->once()
            ->andReturn(['result']);

        $this->assertEquals(['result'], $this->sut->fetchOfficeCopy($licenceId));
    }

    public function testFetchValidLicences(): void
    {
        $licenceId = 1;
        $issueNo = 0;
        $mockQb = m::mock();
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn('foo');
        $mockQb->shouldReceive('andWhere')->with('foo')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->neq')->with('m.issueNo', ':issueNo')->once()->andReturn('bar');
        $mockQb->shouldReceive('andWhere')->with('bar')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.status', ':pending')->once()->andReturn('statuspending');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':active')->once()->andReturn('statusactive');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':withdrawn')->once()->andReturn('statuswithdrawn');
        $mockQb->shouldReceive('expr->eq')->with('m.status', ':suspended')->once()->andReturn('statussuspended');
        $mockQb->shouldReceive('expr->orX')
            ->with(
                'statuspending',
                'statusactive',
                'statuswithdrawn',
                'statussuspended'
            )
            ->once()
            ->andReturn('statuses');
        $mockQb->shouldReceive('andWhere')->with('statuses')->once()->andReturnSelf();
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
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');
        $this->assertEquals('result', $this->sut->fetchValidLicences($licenceId));
    }

    public function testFetchLicencesById(): void
    {
        $mockQb = m::mock();
        $mockQb->shouldReceive('expr->in')->with('m.id', ':ids')->once()->andReturn('id');
        $mockQb->shouldReceive('andWhere')->with('id')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('ids', [1])->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');
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
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn('licence');
        $mockQb->shouldReceive('andWhere')->with('licence')->once()->andReturnSelf();
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
        $mockQb = m::mock();
        $mockQb->shouldReceive('innerJoin')->with('m.communityLicSuspensions', 's')->andReturnSelf();
        $mockQb->shouldReceive('innerJoin')->with('s.communityLicSuspensionReasons', 'sr')->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.status', ':status')->once()->andReturn('status');
        $mockQb->shouldReceive('expr->lte')->with('s.startDate', ':startDate')->once()->andReturn('startDate');
        $mockQb->shouldReceive('andWhere')->with('status')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('startDate')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', CommunityLicEntity::STATUS_ACTIVE)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('startDate', 'foo')->andReturnSelf();
        $mockQb->shouldReceive('expr->gt')->with('s.endDate', ':endDate')->once()->andReturn('endDateGt');
        $mockQb->shouldReceive('expr->isNull')->with('s.endDate')->once()->andReturn('endDateNull');
        $mockQb->shouldReceive('expr->orX')->with('endDateNull', 'endDateGt')->once()->andReturn('orExpr');
        $mockQb->shouldReceive('andWhere')->with('orExpr')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('endDate', 'foo')->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');
        $this->assertEquals('result', $this->sut->fetchForSuspension('foo'));
    }

    public function testFetchForActivation(): void
    {
        $mockQb = m::mock();
        $mockQb->shouldReceive('innerJoin')->with('m.communityLicSuspensions', 's')->andReturnSelf();
        $mockQb->shouldReceive('innerJoin')->with('s.communityLicSuspensionReasons', 'sr')->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.status', ':status')->once()->andReturn('status');
        $mockQb->shouldReceive('expr->lte')->with('s.endDate', ':endDate')->once()->andReturn('endDate');
        $mockQb->shouldReceive('andWhere')->with('status')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('endDate')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', CommunityLicEntity::STATUS_SUSPENDED)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('endDate', 'foo')->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');
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
        $parameterMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('licence', $expectedLicenceId);

        // Expect
        $queryBuilder->shouldReceive('setParameters')->once()->with($parameterMatcher);

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
        $parameterMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('status', CommunityLicEntity::STATUS_ACTIVE);

        // Expect
        $queryBuilder->shouldReceive('setParameters')->once()->with($parameterMatcher);

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
        $parameterMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('issueNo', 0);

        // Expect
        $queryBuilder->shouldReceive('setParameters')->once()->with($parameterMatcher);

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
        $query = m::mock()->shouldIgnoreMissing();
        $query->shouldReceive('getSingleScalarResult')->andReturn($expectedCount = 997);
        $queryBuilder->shouldReceive('getQuery')->andReturn($query);

        // Execute
        $result = $sut->countActiveByLicenceId(1);

        // Assert
        $this->assertEquals($expectedCount, $result);
    }
}
