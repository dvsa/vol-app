<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Doctrine\ORM\QueryBuilder;

/**
 * Readonly Repository Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ReadonlyRepositoryInterface
{
    /**
     * Called from the factory to allow additional services to be injected
     */
    public function initService(RepositoryServiceManager $serviceManager);

    public function fetchUsingId(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT, $version = null);

    public function fetchById($id, $hydrateMode = Query::HYDRATE_OBJECT, $version = null);

    public function fetchByIds(array $ids, $hydrateMode = Query::HYDRATE_OBJECT);

    /**
     * @param int $hydrateMode
     * @return array|\ArrayIterator|\Traversable
     */
    public function fetchList(QueryInterface $query, $hydrateMode = Query::HYDRATE_ARRAY);

    public function fetchPaginatedList(QueryBuilder $qb, $hydrateMode = Query::HYDRATE_ARRAY, QueryInterface $originalQuery = null);

    /**
     * @param int $hydrateMode
     * @return int
     */
    public function fetchCount(QueryInterface $query);

    public function fetchPaginatedCount(QueryBuilder $qb);

    public function lock($entity, $version);

    /**
     * @param $id
     * @return RefDataEntity
     */
    public function getRefdataReference($id);

    /**
     * @param $id
     * @return Category
     */
    public function getCategoryReference($id);

    /**
     * @param $id
     * @return SubCategory
     */
    public function getSubCategoryReference($id);

    /**
     * Get Reference
     *
     * @param class-string<T> $entityClass Entity class FQN
     * @param string|int $id id
     *
     * @return T|null Entity reference
     *
     * @template T
     */
    public function getReference($entityClass, $id);
}
