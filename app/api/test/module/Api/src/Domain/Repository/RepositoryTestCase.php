<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\DbQueryServiceManager;
use Dvsa\Olcs\Api\Domain\QueryBuilder as OlcsQueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryBuilderInterface;
use Dvsa\Olcs\Api\Domain\Repository\AbstractReadonlyRepository;
use Dvsa\Olcs\Api\Domain\Repository\AbstractRepository;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\OlcsTest\Builder\ServiceManagerBuilder;
use Dvsa\OlcsTest\Support\DoctrineMetadata;
use Dvsa\OlcsTest\Support\QueryPartials;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

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

    /** The query builder the test under way is asserting on, set by createRealQb(). */
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

        $this->qb = null;
    }

    protected function mockCreateQueryBuilder(mixed $mock): void
    {
        $this->em->shouldReceive('getRepository->createQueryBuilder')
            ->andReturn($mock);
    }

    /**
     * Build the repository against real Doctrine: real entity metadata, and the query
     * partials the application itself wires, so assertions are made on the DQL the
     * repository really produces. Prefer this over setUpSut() for anything that builds a
     * query — see createRealQb().
     *
     * The EntityManager stays a mock because the repository uses it only to hand out
     * repositories and to persist; the metadata EntityManager the partials read from is a
     * separate, real one, and nothing in either path opens a connection.
     */
    protected function setUpRealSut(mixed $class = null, bool $mockSut = false): void
    {
        $this->em = m::mock(EntityManager::class);
        $this->queryBuilder = new OlcsQueryBuilder(QueryPartials::serviceManager(DoctrineMetadata::entityManager()));
        $this->dbQueryService = m::mock(DbQueryServiceManager::class);

        if ($mockSut) {
            $this->sut = m::mock($class, [$this->em, $this->queryBuilder, $this->dbQueryService])
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        } else {
            $this->sut = new $class($this->em, $this->queryBuilder, $this->dbQueryService);
        }

        $this->qb = null;
    }

    /**
     * A real QueryBuilder rooted on the entity and alias the repository under test declares,
     * wired in as the one createQueryBuilder() hands back. Assert on $qb->getDQL(); declare
     * rows with $qb->willReturn([...]).
     *
     * Pass $entity/$alias only to root somewhere other than the repository's own entity.
     */
    protected function createRealQb(?string $entity = null, ?string $alias = null): TestQueryBuilder
    {
        if ($entity === null || $alias === null) {
            // Bound to the abstract so the protected declarations resolve through any
            // subclass, including a Mockery partial.
            [$sutEntity, $sutAlias] = \Closure::bind(
                fn() => [$this->entity, $this->alias],
                $this->sut,
                AbstractReadonlyRepository::class,
            )();

            $entity ??= $sutEntity;
            $alias ??= $sutAlias;
        }

        $qb = $this->newRealQb();
        $qb->select($alias)->from($entity, $alias);

        $this->mockCreateQueryBuilder($qb);
        $this->qb = $qb;

        return $qb;
    }

    /**
     * Hand out a distinct real QueryBuilder for each entity and alias given, for the repository
     * methods that build more than one. They need separating: a single shared builder would let
     * a sub-select write its own predicates into the outer query.
     *
     * Aliases are unique within a query, so the builders come back keyed by alias:
     *
     *     ['m' => $root, 'gp' => $graceSubSelect] = $this->createRealQbs([
     *         Entity::class => 'm',
     *         GracePeriodEntity::class => 'gp',
     *     ]);
     *
     * @param array<class-string, string|list<string>> $aliasesByEntity
     *
     * @return array<string, TestQueryBuilder>
     */
    protected function createRealQbs(array $aliasesByEntity): array
    {
        $builders = [];

        foreach ($aliasesByEntity as $entity => $aliases) {
            $repository = m::mock(EntityRepository::class);

            foreach ((array) $aliases as $alias) {
                $qb = $this->newRealQb();
                $qb->select($alias)->from($entity, $alias);

                $repository->shouldReceive('createQueryBuilder')->with($alias)->andReturn($qb);
                $builders[$alias] = $qb;
            }

            $this->em->shouldReceive('getRepository')->with($entity)->andReturn($repository);
        }

        return $builders;
    }

    /**
     * A real QueryBuilder with nothing selected and no wiring, for the few repositories that
     * build a query from scratch off the EntityManager rather than through createQueryBuilder().
     */
    protected function newRealQb(): TestQueryBuilder
    {
        return new TestQueryBuilder(DoctrineMetadata::entityManager());
    }

    /**
     * Compile DQL through the real parser, which resolves aliases and field names against the
     * entity metadata. Use it to assert that a query the repository builds is actually valid
     * (or, for a pinned defect, that it is not). TestQueryBuilder cannot do this itself: its
     * getQuery() is stubbed precisely so tests never execute anything.
     */
    protected function compileDql(string $dql): string
    {
        return DoctrineMetadata::entityManager()->createQuery($dql)->getSQL();
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
        return new ServiceManagerBuilder($this->setUpDefaultServices(...))->build();
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

        $expr = m::mock(Expr::class);
        $expr->shouldIgnoreMissing();

        $query = m::mock(Query::class);
        $query->shouldIgnoreMissing();

        $instance->shouldReceive('expr')
            ->andReturn($expr)
            ->byDefault();

        $instance->shouldReceive('getQuery')
            ->andReturn($query)
            ->byDefault();

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
