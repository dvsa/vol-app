<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ObjectRepository;
use Dvsa\Olcs\Api\Domain\DbQueryServiceManager;
use Dvsa\Olcs\Api\Domain\QueryBuilderInterface;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Abstract Readonly Repository
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractReadonlyRepository implements ReadonlyRepositoryInterface
{
    protected $entity = 'Define\Me';

    protected $alias = 'm';

    /**
     * Force paginated queries to fetch joined tables when paginating. Default is true.
     *
     * @see http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/tutorials/pagination.html
     *
     * @var bool
     */
    protected $fetchJoinCollection = true;

    private $references = [];
    /** @var  QueryInterface */
    protected $query;

    /**
     * AbstractReadonlyRepository constructor.
     *
     * @param EntityManagerInterface $em             Entity manager
     * @param QueryBuilderInterface  $queryBuilder   Doctrine Query Builder
     * @param DbQueryServiceManager  $dbQueryManager Olcs Query manager
     */
    public function __construct(private readonly EntityManagerInterface $em, private readonly QueryBuilderInterface $queryBuilder, private readonly DbQueryServiceManager $dbQueryManager)
    {
    }

    /**
     * Disables SoftDeleteable filter, if enabled.
     * If list of entities is provided, the filter will only be disabled for those classes.
     *
     * @param array $entities List of class names
     *
     * @return void
     */
    public function disableSoftDeleteable(array $entities = null)
    {
        if ($this->getEntityManager()->getFilters()->isEnabled('soft-deleteable')) {
            if (!empty($entities)) {
                // disable soft-deleteable filtering for given entities only
                $filter = $this->getEntityManager()->getFilters()->getFilter('soft-deleteable');

                foreach ($entities as $entity) {
                    $filter->disableForEntity($entity);
                }
            } else {
                // disable soft-deleteable filtering for everything
                $this->getEntityManager()->getFilters()->disable('soft-deleteable');
            }
        }
    }

    /**
     * Enables SoftDeleteable filter
     *
     * @return void
     */
    public function enableSoftDeleteable()
    {
        // enable soft-deleteable filtering
        $this->getEntityManager()->getFilters()->enable('soft-deleteable');
    }

    /**
     * Called from the factory to allow additional services to be injected
     *
     * @param RepositoryServiceManager $serviceManager Service manager
     *
     * @return void
     */
    public function initService(RepositoryServiceManager $serviceManager)
    {
        // no-op
    }

    /**
     * _Call
     *
     * @param string $name Method name
     * @param array  $args Method arguments
     *
     * @return array|mixed
     */
    public function __call($name, $args)
    {
        // fetchByFoo => WHERE alias.foo = $args[0]
        if (preg_match('/^fetchBy([a-zA-Z]+)$/', $name, $matches)) {
            return $this->fetchByX(lcfirst($matches[1]), $args);
        }

        if (preg_match('/^fetchOneBy([a-zA-Z]+)$/', $name, $matches)) {
            return $this->fetchOneByX(lcfirst($matches[1]), $args);
        }

        throw new \RuntimeException("Method '{$name}' not found on the Repository.");
    }

    /**
     * Get Olcs Db Query Manager
     *
     * @return DbQueryServiceManager
     */
    protected function getDbQueryManager()
    {
        return $this->dbQueryManager;
    }

    /**
     * Fetch List By Column Name (X) value
     *
     * @param string $fetchBy Column name
     * @param array  $args    [Id, Hydration mode]
     *
     * @return array
     */
    protected function fetchByX($fetchBy, $args)
    {
        $qb = $this->createFetchByXxQuery($fetchBy, $args);

        if (empty($args[1])) {
            $hydrateMode = Query::HYDRATE_OBJECT;
        } else {
            $hydrateMode = $args[1];
        }

        return $qb->getQuery()->getResult($hydrateMode);
    }

    /**
     * Fetch One By Column Name (X) value
     *
     * @param string $fetchBy Column name
     * @param array  $args    [Id, Hydration mode]
     *
     * @return mixed
     * @throws Exception\NotFoundException
     */
    protected function fetchOneByX($fetchBy, $args)
    {
        $qb = $this->createFetchByXxQuery($fetchBy, $args);

        if (empty($args[1])) {
            $hydrateMode = Query::HYDRATE_OBJECT;
        } else {
            $hydrateMode = $args[1];
        }

        try {
            return $qb->getQuery()->getSingleResult($hydrateMode);
        } catch (\Exception) {
            throw new Exception\NotFoundException(
                sprintf('Resource not found (%s %s %s)', $this->entity, $fetchBy, (string)$args[0])
            );
        }
    }

    /**
     * Create Fetch By X-Column Query
     *
     * @param string $fetchBy Column name
     * @param array  $args    [Id, Hydration mode]
     *
     * @return bool|QueryBuilder
     */
    protected function createFetchByXxQuery($fetchBy, $args)
    {
        // If the property doesn't exist
        if (!property_exists($this->entity, $fetchBy)) {
            return false;
        }

        if (empty($args)) {
            return false;
        }

        $value = $args[0];

        $qb = $this->createQueryBuilder();

        match (true) {
            is_array($value) => $qb->andWhere(
                $qb->expr()->in($this->alias . '.' . $fetchBy, $value)
            ),
            is_int($value) => $qb->andWhere(
                $qb->expr()->eq($this->alias . '.' . $fetchBy, $value)
            ),
            is_string($value) => $qb->andWhere(
                $qb->expr()->eq($this->alias . '.' . $fetchBy, ':' . $fetchBy)
            )->setParameter($fetchBy, $value),
            default => $qb,
        };

        return $qb;
    }

    /**
     * Fetch the default record by its id
     *
     * @param Query|QryCmd $query       Http Query
     * @param int          $hydrateMode Hydrate mode
     * @param int          $version     Version
     *
     * @return mixed
     */
    public function fetchUsingId(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        return $this->fetchById($query->getId(), $hydrateMode, $version);
    }

    /**
     * Fetch By Id
     *
     * @param int $id          Id
     * @param int $hydrateMode Hydrate mode
     * @param int $version     Version
     *
     * @return mixed
     * @throws Exception\NotFoundException
     */
    public function fetchById($id, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        // If we are not locking and requesting an object, check the cache first
        $cache = ($version === null && $hydrateMode === Query::HYDRATE_OBJECT);
        if ($cache && isset($this->references[$id])) {
            return $this->references[$id];
        }

        $qb = $this->createQueryBuilder();

        $this->buildDefaultQuery($qb, $id);

        $this->applyFetchJoins($qb);

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException(
                sprintf('Resource not found (%s id %s)', $this->entity, $id)
            );
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        if ($cache) {
            $this->references[$id] = $results[0];
        }

        return $results[0];
    }

    /**
     * Decache By Id
     *
     * @param int $id Id
     */
    public function decacheById($id)
    {
        if (isset($this->references[$id])) {
            unset($this->references[$id]);
        }
    }

    /**
     * Refresh entity from the database
     *
     * @param $entity
     */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
     * Whether this repository contains the specified entity
     *
     * @param $entity
     *
     * @return bool
     */
    public function contains($entity)
    {
        return $this->em->contains($entity);
    }

    /**
     * Fetch by ids
     *
     * @param array $ids         List of Ids
     * @param int   $hydrateMode Hydrate mode
     *
     * @return array
     */
    public function fetchByIds(array $ids, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->filterByIds($ids);

        return $qb->getQuery()->getResult($hydrateMode);
    }

    /**
     * Fetch List
     *
     * @param QueryInterface $query       Http Query
     * @param int            $hydrateMode Hydrate mode
     *
     * @return \ArrayIterator|\Traversable
     */
    public function fetchList(QueryInterface $query, $hydrateMode = Query::HYDRATE_ARRAY)
    {
        $this->query = $query;

        $qb = $this->createQueryBuilder();

        $this->buildDefaultListQuery($qb, $query);
        $this->applyListJoins($qb);
        $this->applyListFilters($qb, $query);
        return $this->fetchPaginatedList($qb, $hydrateMode);
    }

    /**
     * Abstracted paginator logic so it can be re-used with alternative queries
     *
     * @param QueryBuilder $qb              Doctrine query builder
     * @param int          $hydrateMode     Hydrate mode
     * @param QueryInterface $originalQuery Original query
     *
     * @return \ArrayIterator|\Traversable
     */
    public function fetchPaginatedList(QueryBuilder $qb, $hydrateMode = Query::HYDRATE_ARRAY, QueryInterface $originalQuery = null)
    {
        $query = $qb->getQuery();
        $query->setHydrationMode($hydrateMode);

        if ($this->query instanceof PagedQueryInterface || ($originalQuery instanceof PagedQueryInterface)) {
            $paginator = $this->getPaginator($query);

            return $paginator->getIterator();
        }

        return new \ArrayIterator($query->getResult($hydrateMode));
    }

    /**
     * Fetch Count of records
     *
     * @param QueryInterface $query Http query
     *
     * @return int
     */
    public function fetchCount(QueryInterface $query)
    {
        $qb = $this->createQueryBuilder();

        $this->buildDefaultListQuery($qb, $query);
        $this->applyListJoins($qb);
        $this->applyListFilters($qb, $query);

        // order is not important for count but slows down the query (a lot!)
        $qb->resetDQLPart('orderBy');

        return $this->fetchPaginatedCount($qb);
    }

    /**
     * Does the have any rows
     *
     * @param QueryInterface $query Http Query
     *
     * @return bool
     */
    public function hasRows(QueryInterface $query)
    {
        $qb = $this->createQueryBuilder();

        $this->buildDefaultListQuery($qb, $query);
        $this->applyListJoins($qb);
        $this->applyListFilters($qb, $query);

        // order is not important for count but slows down the query (a lot!)
        $qb->resetDQLPart('orderBy');

        $qb->setMaxResults(1);

        return count($qb->getQuery()->getResult()) === 1;
    }

    /**
     * Abstracted the count functionality so it can be re-used with alternative queries
     *
     * @param QueryBuilder $qb Doctrine query builder
     *
     * @return int
     */
    public function fetchPaginatedCount(QueryBuilder $qb)
    {
        return (int)$this->getPaginator($qb)->count();
    }

    /**
     * Apply Filters to list Query
     *
     * @param QueryBuilder   $qb    Doctrine query builder
     * @param QueryInterface $query Http query
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
    }

    /**
     * Override to add additional data to the default fetchList() method
     * Join tables to query by conditions
     *
     * @param QueryBuilder $qb Doctrine query builder
     *
     * @return void
     * @inheritdoc
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
    }

    /**
     * Override to add additional data to the default fetchById() method
     *
     * @param QueryBuilder $qb Doctrine query builder
     *
     * @return void
     * @inheritdoc
     */
    protected function applyFetchJoins(QueryBuilder $qb)
    {
    }

    /**
     * Lock
     *
     * @param Entity $entity  Entity
     * @param int    $version Version
     *
     * @return void
     * @throws Exception\RuntimeException
     * @throws Exception\VersionConflictException
     */
    public function lock($entity, $version)
    {
        if (!($entity instanceof $this->entity)) {
            throw new Exception\RuntimeException('This repository can only lock entities of type ' . $this->entity);
        }

        try {
            $this->getEntityManager()->lock($entity, LockMode::OPTIMISTIC, $version);
        } catch (OptimisticLockException) {
            throw new Exception\VersionConflictException();
        }
    }

    /**
     * Get reference on RefData entity
     *
     * @param string $id Id
     *
     * @return RefDataEntity|null
     */
    public function getRefdataReference($id)
    {
        return $this->getReference(RefDataEntity::class, $id);
    }

    /**
     * Get reference on Category entity
     *
     * @param int $id Id
     *
     * @return Category|null
     */
    public function getCategoryReference($id)
    {
        return $this->getReference(Category::class, $id);
    }

    /**
     * Get reference on Category entity
     *
     * @param string $id Id
     *
     * @return SubCategory|null
     */
    public function getSubCategoryReference($id)
    {
        return $this->getReference(SubCategory::class, $id);
    }

    /**
     * Get Reference
     *
     * @param class-string<T> $entityClass Entity class FQN
     * @param string|int $id id
     *
     * @return T|null The entity reference.
     *
     * @template T
     * @throws ORMException
     */
    public function getReference($entityClass, $id)
    {
        return !empty($id) ? $this->getEntityManager()->getReference($entityClass, $id) : null;
    }

    /**
     * GenerateRefdataArrayCollection
     *
     * @param array $ids Id
     *
     * @return ArrayCollection
     */
    public function generateRefdataArrayCollection($ids)
    {
        $refDataArray = [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $refDataArray[] = $this->getRefdataReference($id);
            }
        }
        return new ArrayCollection($refDataArray);
    }

    /**
     * Returns Entity Manager
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Returns Olcs Query Builder
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Create and returns Doctrine Query Builder
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->getRepository()->createQueryBuilder($this->alias);
    }

    /**
     * Returns repository
     *
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->entity);
    }

    /**
     * Build Default Query
     * This method can be overridden to extend the default resource bundle
     *
     * @param QueryBuilder $qb Doctrine query builder
     * @param int          $id Identifier
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        return $this->getQueryBuilder()->modifyQuery($qb)->withRefdata()->byId($id);
    }

    /**
     * Create Doctrine List Query builder
     *
     * @param QueryInterface $query Http query
     *
     * @return QueryBuilder
     */
    protected function createDefaultListQuery(QueryInterface $query)
    {
        $qb = $this->createQueryBuilder();
        $this->buildDefaultListQuery($qb, $query);

        return $qb;
    }

    /**
     * Assign paginator and order statement to query
     *
     * @param QueryBuilder   $qb              Doctrine query builder
     * @param QueryInterface $query           Http query
     * @param array          $compositeFields Composite Fields
     *
     * @return void
     */
    protected function buildDefaultListQuery(QueryBuilder $qb, QueryInterface $query, $compositeFields = [])
    {
        $queryBuilderHelper = $this->getQueryBuilder()->modifyQuery($qb);
        $queryBuilderHelper->withRefdata();

        if ($query instanceof PagedQueryInterface) {
            $queryBuilderHelper->paginate($query->getPage(), $query->getLimit());
        }

        if ($query instanceof OrderedQueryInterface) {
            if (!$query->isSortWhitelisted()) {
                throw new \Exception("The 'sort' value is not valid.");
            }
            if (!empty($query->getSort())) {
                // allow ordering by multiple columns
                $sortColumns = explode(',', $query->getSort());
                $orderColumns = explode(',', $query->getOrder());

                foreach ($sortColumns as $i => $column) {
                    // if multiple order value doesn't exist then use the first one
                    $order = $orderColumns[$i] ?? $orderColumns[0];

                    $queryBuilderHelper->order($column, $order, $compositeFields);
                }
            }
        }
    }

    /**
     * Wrap paginator instantiation, mainly for unit testing
     *
     * @param \Doctrine\ORM\Query $query Doctrine query
     *
     * @return Paginator
     */
    protected function getPaginator($query)
    {
        return new Paginator($query, $this->fetchJoinCollection);
    }

    /**
     * Builds a string identifier for a column.
     *
     * @return string
     */
    protected function getColumnIdentifier(string $columnName): string
    {
        return sprintf('%s.%s', $this->alias, $columnName);
    }
}
