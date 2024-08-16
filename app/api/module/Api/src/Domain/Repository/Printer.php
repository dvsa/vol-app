<?php

/**
 * Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\PrintScan\Printer as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Printer extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchWithTeams($id)
    {
        $qb = $this->createQueryBuilder();
        $this->buildDefaultQuery($qb, $id)
            ->with('teamPrinters');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->orderBy($this->alias . '.printerName', 'ASC');
    }
}
