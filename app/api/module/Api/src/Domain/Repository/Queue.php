<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Queue\Queue as Entity;

/**
 * Queue
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Queue extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'q';

    /**
     * Enqueue CNS
     *
     * @param array $licences licences
     *
     * @return int
     */
    public function enqueueContinuationNotSought($licences)
    {
        /**
         * @var \Doctrine\DBAL\Connection $conn
         */
        $conn = $this->getEntityManager()->getConnection();

        $query = 'INSERT INTO `queue` (`status`, `type`, `options`) VALUES ';

        for ($i = 1, $n = count($licences); $i <= $n; $i++) {
            $query .= "(:status{$i}, :type{$i}, :options{$i}), ";
        }
        $query = trim($query, ', ');
        $stmt = $conn->prepare($query);

        $i = 1;
        foreach ($licences as $licence) {
            $stmt->bindValue('status' . $i, Entity::STATUS_QUEUED);
            $stmt->bindValue('type' . $i, Entity::TYPE_CNS);
            $stmt->bindValue('options' . $i, '{"id":' . $licence['id'] . ',"version":' . $licence['version'] . '}');
            $i++;
        }

        $result = $stmt->executeQuery();
        return $result->rowCount();
    }

    /**
     * Get next item
     * AND increment attempts
     *
     * @param array $includeTypes Types to include, default include all
     * @param array $excludeTypes Types to exclude, default exclude none
     *
     * @return Entity
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getNextItem(array $includeTypes = [], array $excludeTypes = [])
    {
        $qb = $this->getNextItemQueryBuilder();

        $result = $this->getNextItemWithQueryBuilder($includeTypes, $excludeTypes, $qb);

        if (!is_null($result)) {
            $result->incrementAttempts();
            $result->setStatus($this->getRefdataReference(Entity::STATUS_PROCESSING));
            $this->save($result);
        }

        return $result;
    }

    /**
     * Fetch next item even if postponed
     * and DO NOT increment attempts
     *
     * @param array $includeTypes Types to include, default include all
     * @param array $excludeTypes Types to exclude, default exclude none
     *
     * @return Entity
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fetchNextItemIncludingPostponed(array $includeTypes = [], array $excludeTypes = [])
    {
        $qb = $this->fetchNextItemIncludingPostponedQueryBuilder();

        return $this->getNextItemWithQueryBuilder($includeTypes, $excludeTypes, $qb);
    }

    /**
     * Get next item
     *
     * @param array        $includeTypes Types to include, default include all
     * @param array        $excludeTypes Types to exclude, default exclude none
     * @param QueryBuilder $qb           Query builder
     *
     * @return Entity
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function getNextItemWithQueryBuilder($includeTypes, $excludeTypes, \Doctrine\ORM\QueryBuilder $qb)
    {

        if (!empty($includeTypes)) {
            $qb
                ->andWhere($qb->expr()->in($this->alias . '.type', ':includeTypes'))
                ->setParameter('includeTypes', $includeTypes);
        }

        if (!empty($excludeTypes)) {
            $qb
                ->andWhere($qb->expr()->notIn($this->alias . '.type', ':excludeTypes'))
                ->setParameter('excludeTypes', $excludeTypes);
        }

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            return null;
        }

        return $results[0];
    }

    /**
     * Is there an item of type already queued?
     *
     * @param string $type Queue::TYPE_ contant
     *
     * @return bool
     */
    public function isItemTypeQueued($type)
    {
        $qb = $this->getNextItemQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.type', ':type'))
            ->setParameter('type', $type);

        $results = $qb->getQuery()->getArrayResult();

        return !empty($results);
    }

    /**
     * Is there an item of type/status already in the queue
     *
     * @param array $types    Queue::TYPE_
     * @param array $statuses Queue::STATUS_
     *
     * @return bool
     */
    public function isItemInQueue(array $types, array $statuses)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias . '.id')
            ->andWhere($qb->expr()->in($this->alias . '.type', ':types'))
            ->andWhere($qb->expr()->in($this->alias . '.status', ':statuses'))
            ->setParameter('types', $types)
            ->setParameter('statuses', $statuses)
            ->setMaxResults(1);

        $results = $qb->getQuery()->getArrayResult();

        return !empty($results);
    }

    /**
     * Get the QueryBuilder for getting the next item
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getNextItemQueryBuilder()
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->order('id', 'ASC');

        $now = new DateTime();
        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':statusId'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->lte($this->alias . '.processAfterDate', ':processAfter'),
                    $qb->expr()->isNull($this->alias . '.processAfterDate')
                )
            )
            ->setParameter('statusId', Entity::STATUS_QUEUED)
            ->setParameter('processAfter', $now)
            ->setMaxResults(1);

        return $qb;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function fetchNextItemIncludingPostponedQueryBuilder()
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->order($this->alias . '.processAfterDate', 'ASC');

        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':statusId'))
            ->setParameter('statusId', Entity::STATUS_QUEUED)
            ->setMaxResults(1);

        return $qb;
    }
}
