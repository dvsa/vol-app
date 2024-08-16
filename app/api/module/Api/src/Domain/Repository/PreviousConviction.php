<?php

/**
 * Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PreviousConviction extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'pc';

    /**
     * Fetch a list of Previous Convictions for a Transport Manager
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

        $dqb->andWhere($dqb->expr()->eq('pc.transportManager', ':tmId'))
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
}
