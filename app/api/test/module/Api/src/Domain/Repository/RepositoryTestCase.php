<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\DbQueryServiceManager;
use Dvsa\Olcs\Api\Domain\QueryBuilderInterface;
use Dvsa\Olcs\Api\Domain\Repository\AbstractRepository;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\OlcsTest\Builder\ServiceManagerBuilder;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

/**
 * Repository Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RepositoryTestCase extends MockeryTestCase
{
    /**
     * @var m\MockInterface|RepositoryInterface
     */
    protected $sut;

    /**
     * @var m\MockInterface|EntityManager
     */
    protected $em;

    /**
     * @var m\MockInterface
     */
    protected $queryBuilder;

    /**
     * @var m\MockInterface|DbQueryServiceManager
     */
    protected $dbQueryService;

    protected $query = '';
    protected $qb;

    public function setUpSut(mixed $class = null, bool $mockSut = false): void
    {
        $this->em = m::mock(EntityManager::class);
        $this->queryBuilder = m::mock(QueryBuilderInterface::class);
        $this->dbQueryService = m::mock(DbQueryServiceManager::class);

        if ($mockSut) {
            $this->sut = m::mock($class, [$this->em, $this->queryBuilder, $this->dbQueryService])
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        } else {
            $this->sut = new $class($this->em, $this->queryBuilder, $this->dbQueryService);
        }

        $this->query = '';
        $this->qb = null;
    }

    protected function mockCreateQueryBuilder(mixed $mock): void
    {
        $this->em->shouldReceive('getRepository->createQueryBuilder')
            ->andReturn($mock);
    }

    /**
     * @return m\MockInterface
     */
    protected function createMockQb(string $query = ''): mixed
    {
        $this->query = $query;

        $this->qb = m::mock(QueryBuilder::class);

        $this->qb->shouldReceive('expr->eq')
            ->andReturnUsing($this->mockExprEq(...));

        $this->qb->shouldReceive('expr->neq')
            ->andReturnUsing($this->mockExprNeq(...));

        $this->qb->shouldReceive('expr->lte')
            ->andReturnUsing($this->mockExprLte(...));

        $this->qb->shouldReceive('expr->lt')
            ->andReturnUsing($this->mockExprLt(...));

        $this->qb->shouldReceive('expr->gte')
            ->andReturnUsing($this->mockExprGte(...));

        $this->qb->shouldReceive('expr->gt')
            ->andReturnUsing($this->mockExprGt(...));

        $this->qb->shouldReceive('expr->isNull')
            ->andReturnUsing($this->mockExprIsNull(...));

        $this->qb->shouldReceive('expr->between')
            ->andReturnUsing($this->mockExprBetween(...));

        $this->qb->shouldReceive('expr->in')
            ->andReturnUsing($this->mockExprIn(...));

        $this->qb->shouldReceive('expr->notIn')
            ->andReturnUsing($this->mockExprNotIn(...));

        $this->qb->shouldReceive('expr->isNotNull')
            ->andReturnUsing($this->mockExprIsNotNull(...));

        $this->qb->shouldReceive('expr->like')
            ->andReturnUsing($this->mockExprLike(...));

        $this->qb->shouldReceive('expr->orX')
            ->andReturnUsing($this->mockOrX(...));

        $this->qb->shouldReceive('expr->andX')
            ->andReturnUsing($this->mockAndX(...));

        $this->qb->shouldReceive('expr->count')
            ->andReturnUsing($this->mockCount(...));

        $this->qb->shouldReceive('select')
            ->andReturnUsing($this->mockAddSelect(...));

        $this->qb->shouldReceive('distinct')
            ->andReturnUsing($this->mockDistinct(...));

        $this->qb->shouldReceive('addSelect')
            ->andReturnUsing($this->mockAddSelect(...));

        $this->qb->shouldReceive('select')
            ->andReturnUsing($this->mockAddSelect(...));

        $this->qb->shouldReceive('where')
            ->andReturnUsing($this->mockAndWhere(...));

        $this->qb->shouldReceive('andWhere')
            ->andReturnUsing($this->mockAndWhere(...));

        $this->qb->shouldReceive('orWhere')
            ->andReturnUsing($this->mockOrWhere(...));

        $this->qb->shouldReceive('join')
            ->andReturnUsing($this->mockInnerJoin(...));

        $this->qb->shouldReceive('innerJoin')
            ->andReturnUsing($this->mockInnerJoin(...));

        $this->qb->shouldReceive('leftJoin')
            ->andReturnUsing($this->mockLeftJoin(...));

        $this->qb->shouldReceive('orderBy')
            ->andReturnUsing($this->mockOrderBy(...));

        $this->qb->shouldReceive('addOrderBy')
            ->andReturnUsing($this->mockOrderBy(...));

        $this->qb->shouldReceive('groupBy')
            ->andReturnUsing($this->mockGroupBy(...));

        $this->qb->shouldReceive('setParameter')
            ->andReturnUsing($this->mockSetParameter(...));

        $this->qb->shouldReceive('setMaxResults')
            ->andReturnUsing($this->mockSetMaxResults(...));

        $this->qb->shouldReceive('distinct')
            ->andReturnUsing($this->mockDistinct(...));

        return $this->qb;
    }

    public function mockOrderBy(mixed $sort, mixed $order): mixed
    {
        $this->query .= ' ORDER BY ' . $sort . ' ' . $order;

        return $this->qb;
    }

    public function mockGroupBy(mixed $field): mixed
    {
        $fields = func_get_args();
        if (func_num_args() === 1 && is_array($field)) {
            $fields = $field;
        }

        $this->query .= ' GROUP BY ' . implode(', ', $fields);

        return $this->qb;
    }

    public function mockSetMaxResults(mixed $maxResults): mixed
    {
        $this->query .= ' LIMIT ' . $maxResults;

        return $this->qb;
    }

    public function mockSetParameter(mixed $name, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        $this->query = str_replace(':' . $name, '[[' . $value . ']]', $this->query);

        return $this->qb;
    }

    public function mockDistinct(): mixed
    {
        $this->query .= ' DISTINCT';

        return $this->qb;
    }

    public function mockAddSelect(mixed $select): mixed
    {
        $selects = func_get_args();
        if (func_num_args() === 1 && is_array($select)) {
            $selects = $select;
        }

        $this->query .= ' SELECT ' . implode(', ', $selects);

        return $this->qb;
    }

    public function mockAndWhere(mixed $where): mixed
    {
        $this->query .= ' AND ' . $where;
        return $this->qb;
    }

    public function mockOrWhere(mixed $where): mixed
    {
        $this->query .= ' OR ' . $where;

        return $this->qb;
    }

    public function mockInnerJoin(mixed $field, mixed $alias, mixed $type = null, mixed $condition = null): mixed
    {
        $this->query .= ' INNER JOIN ' . $field . ' ' . $alias;

        if ($condition !== null) {
            $this->query .= ' ' . $type;
            $this->query .= ' ' . $condition;
        }

        return $this->qb;
    }

    public function mockLeftJoin(mixed $field, mixed $alias, mixed $type = null, mixed $condition = null): mixed
    {
        $this->query .= ' LEFT JOIN ' . $field . ' ' . $alias;

        if ($condition !== null) {
            $this->query .= ' ' . $type;
            $this->query .= ' ' . $condition;
        }

        return $this->qb;
    }

    public function mockExprEq(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' = ' . $value;
    }

    public function mockExprNeq(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' != ' . $value;
    }

    public function mockExprLte(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' <= ' . $value;
    }

    public function mockExprLt(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' < ' . $value;
    }

    public function mockExprGte(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' >= ' . $value;
    }

    public function mockExprGt(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' > ' . $value;
    }

    public function mockExprBetween(mixed $field, mixed $from, mixed $to): mixed
    {
        $from = $this->formatValue($from);
        $to = $this->formatValue($to);

        return $field . ' BETWEEN ' . $from . ' AND ' . $to;
    }

    public function mockExprIn(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' IN ' . $value;
    }

    public function mockExprNotIn(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' NOT IN ' . $value;
    }

    public function mockExprIsNull(mixed $field): mixed
    {
        return $field . ' IS NULL';
    }

    public function mockExprIsNotNull(mixed $field): mixed
    {
        return $field . ' IS NOT NULL';
    }

    public function mockExprLike(mixed $field, mixed $value): mixed
    {
        $value = $this->formatValue($value);

        return $field . ' LIKE ' . $value;
    }

    public function mockOrX(): mixed
    {
        return '(' . implode(' OR ', func_get_args()) . ')';
    }

    public function mockAndX(): mixed
    {
        return '(' . implode(' AND ', func_get_args()) . ')';
    }

    public function mockCount(mixed $countable): mixed
    {
        return sprintf('COUNT(%s)', $countable);
    }

    protected function formatValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        if ($value instanceof \DateTime) {
            return $value->format(\DateTime::W3C);
        }

        if ($value instanceof \Dvsa\Olcs\Api\Entity\System\RefData) {
            return $value->getId();
        }

        if (is_object($value)) {
            $value = $value::class;
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return $value;
    }

    protected function expectQueryWithData(mixed $queryName, array $data = [], array $types = [], mixed $queryResponse = null): void
    {
        $query = m::mock();
        if (!$types) {
            $query->shouldReceive('execute')
                ->once()
                ->with($data)
                ->andReturn($queryResponse);
        } else {
            $query->shouldReceive('execute')
                ->once()
                ->with($data, $types)
                ->andReturn($queryResponse);
        }

        $this->dbQueryService->shouldReceive('get')
            ->with($queryName)
            ->andReturn($query);
    }

    /**
     * @return ServiceManager
     */
    protected function setUpServiceManager(): ServiceManager
    {
        return (new ServiceManagerBuilder($this->setUpDefaultServices(...)))->build();
    }

    /**
     * @return array
     */
    public function setUpDefaultServices(ServiceLocatorInterface $serviceLocator): array
    {
        return [
            DbQueryServiceManager::class => $this->setUpDbQueryServiceManager(),
            EntityManager::class => $this->setUpEntityManager($serviceLocator),
            QueryBuilder::class => $this->setUpQueryBuilder(),
        ];
    }

    /**
     * @return MockInterface|DbQueryServiceManager
     */
    protected function setUpDbQueryServiceManager(): MockInterface
    {
        $dbQueryManager = m::mock(DbQueryServiceManager::class);
        $dbQueryManager->shouldIgnoreMissing($dbQueryManager);
        return $dbQueryManager;
    }

    /**
     * @return MockInterface|QueryBuilder|QueryBuilderInterface
     */
    protected function setUpQueryBuilder(): MockInterface
    {
        $instance = m::mock(QueryBuilder::class, QueryBuilderInterface::class);
        $instance->shouldIgnoreMissing($instance);
        $query = m::mock()->shouldIgnoreMissing();
        $instance->shouldReceive('getQuery')->andReturn($query)->byDefault();
        return $instance;
    }

    /**
     * @return MockInterface|EntityManager
     */
    protected function setUpEntityManager(ServiceLocatorInterface $serviceLocator): MockInterface
    {
        $instance = m::mock(EntityManager::class);
        $instance->shouldIgnoreMissing();
        $instance->shouldReceive('getRepository->createQueryBuilder')->andReturnUsing(fn() => $serviceLocator->get(QueryBuilder::class))->byDefault();
        return $instance;
    }

    /**
     * @return MockInterface
     */
    protected function resolveMockService(ServiceLocatorInterface $serviceLocator, string $key): MockInterface
    {
        return $serviceLocator->get($key);
    }

    /**
     * @return AbstractRepository
     */
    protected function setUpRepository(ServiceLocatorInterface $serviceLocator, string $class): AbstractRepository
    {
        $entityManager = $this->resolveMockService($serviceLocator, EntityManager::class);
        $dbQueryManager = $this->resolveMockService($serviceLocator, DbQueryServiceManager::class);

        // We will create a separate query builder instance here. Its best to avoid using this query builder instance
        // because its only a single instance which could be re-used across queries; its a better practice to instead
        // create new query builder instances through the entity manager.
        $queryBuilder = $this->setUpQueryBuilder();

        return new $class($entityManager, $queryBuilder, $dbQueryManager);
    }
}
