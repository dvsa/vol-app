<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity;
use Doctrine\ORM\Query\Expr;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class DataService extends AbstractRepository
{
    /**
     * Fetch Application Statuses
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function fetchApplicationStatus(QueryInterface $query)
    {
        $this->entity = Entity\System\RefData::class;

        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb);

        $qb->innerJoin(
            Entity\Application\Application::class,
            'a',
            Expr\Join::WITH,
            'a.status = ' . $this->alias . '.id'
        );

        if (method_exists($query, 'getOrganisation') && (int)$query->getOrganisation() > 0) {
            $qb
                ->innerJoin('a.licence', 'l', Expr\Join::WITH, $qb->expr()->eq('l.organisation', ':ORG_ID'))
                ->setParameter('ORG_ID', $query->getOrganisation());
        }

        return $qb->getQuery()->execute();
    }
}
