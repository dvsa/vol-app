<?php

/**
 * IrfoPermitStock
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query\QueryException;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as Entity;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;

/**
 * IrfoPermitStock
 */
class IrfoPermitStock extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.irfoCountry', ':byIrfoCountry'));
        $qb->setParameter('byIrfoCountry', $query->getIrfoCountry());

        $qb->andWhere($qb->expr()->eq($this->alias . '.validForYear', ':byValidForYear'));
        $qb->setParameter('byValidForYear', $query->getValidForYear());

        if ($query->getStatus() !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':byStatus'));
            $qb->setParameter('byStatus', $query->getStatus());
        }

        $this->getQueryBuilder()->modifyQuery($qb)->with('status');
    }

    /**
     * Fetch all existing records using Serial Number Start / End
     *
     * @param Query|QryCmd $query
     * @return mixed
     * @throws QueryException
     */
    public function fetchUsingSerialNoStartEnd(QryCmd $query)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.irfoCountry', ':byIrfoCountry'));
        $qb->setParameter('byIrfoCountry', $query->getIrfoCountry());

        $qb->andWhere($qb->expr()->eq($this->alias . '.validForYear', ':byValidForYear'));
        $qb->setParameter('byValidForYear', $query->getValidForYear());

        $qb->andWhere($qb->expr()->gte($this->alias . '.serialNo', ':bySerialNoStart'));
        $qb->setParameter('bySerialNoStart', $query->getSerialNoStart());

        $qb->andWhere($qb->expr()->lte($this->alias . '.serialNo', ':bySerialNoEnd'));
        $qb->setParameter('bySerialNoEnd', $query->getSerialNoEnd());

        $qb->indexBy($this->alias, $this->alias . '.serialNo');

        $results = $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);

        return $results;
    }
}
