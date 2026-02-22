<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Phone Contact
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PhoneContact extends AbstractRepository
{
    protected $entity = Entity\ContactDetails\PhoneContact::class;
    protected $alias = 'pc';

    /**
     * Build Default List Query
     *
     * @param QueryBuilder   $qb              QueryBuilder
     * @param QueryInterface $query           Query
     * @param array          $compositeFields Composite Fields
     *
     * @return void
     */
    #[\Override]
    public function buildDefaultListQuery(QueryBuilder $qb, QueryInterface $query, $compositeFields = [])
    {
        // add calculated columns to allow ordering by them
        parent::buildDefaultListQuery($qb, $query, ['_type']);

        $queryBuilderHelper = $this->getQueryBuilder();
        $queryBuilderHelper->with('phoneContactType', 'pct');
        $qb->addSelect('pct.displayOrder as HIDDEN _type');
    }

    /**
     * Set custom criteria
     *
     * @param QueryBuilder                                   $qb    Query Builder
     * @param TransferQry\ContactDetail\PhoneContact\GetList $query Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($this->alias . '.contactDetails = :CONTACT_DETAILS_ID');
        $qb->setParameter('CONTACT_DETAILS_ID', $query->getContactDetailsId());
    }
}
