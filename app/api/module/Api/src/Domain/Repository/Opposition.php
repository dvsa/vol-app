<?php

/**
 * Opposition
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Opposition\Opposition as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Opposition
 */
class Opposition extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param int          $id
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        parent::buildDefaultQuery($qb, $id);

        return $this->getQueryBuilder()
            ->with('opposer', 'o')
            ->with('grounds')
            ->withPersonContactDetails('o.contactDetails', 'c');
    }

    public function fetchByApplicationId($applicationId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('case', 'c')
            ->order('createdOn', 'DESC');

        $qb->andWhere($qb->expr()->eq('c.application', ':application'))
            ->setParameter('application', $applicationId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     * @param array $compositeFields
     */
    #[\Override]
    protected function buildDefaultListQuery(QueryBuilder $qb, QueryInterface $query, $compositeFields = [])
    {
        parent::buildDefaultListQuery($qb, $query, $compositeFields);

        $this->getQueryBuilder()
            ->with('case', 'ca')
            ->with('opposer', 'o')
            ->withPersonContactDetails('o.contactDetails');
    }

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getCase()) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
                ->setParameter('byCase', $query->getCase());
        }

        if ($query->getLicence()) {
            $qb->andWhere($qb->expr()->eq('ca.licence', ':licence'))
                ->setParameter('licence', $query->getLicence());
        }

        if ($query->getApplication()) {
            $qb->andWhere($qb->expr()->eq('ca.application', ':application'))
                ->setParameter('application', $query->getApplication());
        }
    }
}
