<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Driver\PDO\Result as PDOResult;
use Dvsa\Olcs\Api\Entity\DataRetentionRule as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\DBAL\Driver\PDO\Connection as PDOConnection;

/**
 * DataRetentionRule
 */
class DataRetentionRule extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch as list of enabled data retention rules
     *
     * @param QueryInterface $query    Query from API
     * @param bool           $isReview Only retrieve rules in Review status
     *
     * @return array
     */
    public function fetchEnabledRules(QueryInterface $query = null, $isReview = false)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb);

        $qb->andWhere($qb->expr()->eq($this->alias . '.isEnabled', 1));
        $qb->andWhere($qb->expr()->isNull($this->alias . '.deletedDate'));

        if ($isReview) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.actionType', ':actionType'));
            $qb->setParameter('actionType', 'Review');
        }

        if (!is_null($query)) {
            $this->buildDefaultListQuery($qb, $query);
        }

        return [
            'results' => $qb->getQuery()->getResult(),
            'count' => $this->getPaginator($qb)->count()
        ];
    }

    /**
     * Fetch a list of all data retention rules
     *
     * @param QueryInterface $query Query from API
     *
     * @return array
     */
    public function fetchAllRules(QueryInterface $query = null)
    {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb);

        if (!is_null($query)) {
            $this->buildDefaultListQuery($qb, $query);
        }

        return [
            'results' => $qb->getQuery()->getResult(),
            'count' => $this->getPaginator($qb->getQuery())->count()
        ];
    }

    /**
     * Run a stored proc
     *
     * @param string $storedProc The stored proc to run
     * @param int    $userId     User ID os the user running the proc
     */
    public function runProc($storedProc, $userId): bool
    {
        $callString = sprintf('CALL %s(%d)', $storedProc, (int)$userId);

        /** @var PDOConnection $connection */
        $connection = $this->getEntityManager()->getConnection()->getNativeConnection();

        /** @var \PDOStatement $stmt */
        $stmt = $connection->prepare($callString);

        $result = $stmt->execute();

        do {
            /**
             * OLCS-18968
             * multiple record sets will be returned
             * rowCount to avoid empty loop
             */
            $stmt->rowCount();
        } while ($stmt->nextRowset());

        $stmt->closeCursor();
        return $result;
    }
}
