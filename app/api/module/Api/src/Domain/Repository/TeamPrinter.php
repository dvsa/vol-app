<?php

/**
 * TeamPrinter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter as Entity;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TeamPrinter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamPrinter extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getTeam()) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.team', ':team'))
                ->setParameter('team', $query->getTeam());
        }
        $qb->andWhere(
            $qb->expr()->not(
                $qb->expr()->andX(
                    $qb->expr()->isNull('sc.id'),
                    $qb->expr()->isNull('u.id')
                )
            )
        );

        $qb->addSelect('CONCAT(ucdp.forename, ucdp.familyName) as HIDDEN userSort');
        $qb->addSelect('CONCAT(scc.description, sc.subCategoryName) as HIDDEN catSort');
        $qb->addOrderBy('t.name', 'ASC');
        $qb->addOrderBy('userSort', 'ASC');
        $qb->addOrderBy('catSort', 'ASC');
    }

    /**
     *
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('subCategory', 'sc')
            ->with('sc.category', 'scc')
            ->with('user', 'u')
            ->with('team', 't')
            ->with('u.contactDetails', 'ucd')
            ->with('ucd.person', 'ucdp');
    }

    /**
     * Fetch TeamPrinter by provided details
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface
     * @return array
     */
    public function fetchByDetails($command)
    {
        $qb = $this->createQueryBuilder();

        if ($command->getSubCategory()) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.subCategory', ':subCategory'))
                ->setParameter('subCategory', $command->getSubCategory());
        } else {
            $qb->andWhere($qb->expr()->isNull($this->alias . '.subCategory'));
        }

        if ($command->getUser()) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.user', ':user'))
                ->setParameter('user', $command->getUser());
        } else {
            $qb->andWhere($qb->expr()->isNull($this->alias . '.user'));
        }

        $qb->andWhere($qb->expr()->eq($this->alias . '.team', ':team'))
            ->setParameter('team', $command->getTeam());

        return $qb->getQuery()->getResult();
    }
}
