<?php

/**
 * OtherLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * OtherLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OtherLicence extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'ol';

    /**
     * Fetch a list of Other Licences for a Transport Manager
     *
     * @param int $tmId
     *
     * @return array
     */
    public function fetchByTransportManager($tmId)
    {
        $dqb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($dqb)
            ->withRefdata();

        $dqb->andWhere($dqb->expr()->eq($this->alias . '.transportManager', ':tmId'))
            ->setParameter('tmId', $tmId);

        return $dqb->getQuery()->getResult();
    }

    /**
     * Filter list
     *
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getTransportManager()) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.transportManager', ':tmId'))
                ->setParameter('tmId', $query->getTransportManager());
        }
    }

    public function fetchForTransportManagerApplication($transportManagerApplicationId)
    {
        return $this->fetchForTransportManagerApplicationOrLicence(
            $transportManagerApplicationId,
            'transportManagerApplication'
        );
    }

    public function fetchForTransportManagerLicence($transportManagerLicenceId)
    {
        return $this->fetchForTransportManagerApplicationOrLicence(
            $transportManagerLicenceId,
            'transportManagerLicence'
        );
    }

    protected function fetchForTransportManagerApplicationOrLicence($id, $field)
    {
        $qb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata();

        $qb->andWhere($qb->expr()->eq($this->alias . '.' . $field, ':id'))
            ->setParameter('id', $id);

        return $qb->getQuery()->getResult();
    }
}
