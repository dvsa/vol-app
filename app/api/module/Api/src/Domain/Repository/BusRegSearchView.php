<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\View\BusRegSearchView as Entity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as ListQueryObject;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query as DoctrineQuery;
use Doctrine\ORM\Query\Expr\Join;

/**
 * BusRegSearchView
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BusRegSearchView extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Setting to false removes the unnecessary DISTINCT clause from pagination queries
     * @see http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/tutorials/pagination.html
     *
     * @var bool
     */
    protected $fetchJoinCollection = false;

    /**
     * Fetch an entry from the view for a Reg No
     *
     * @param string $regNo
     *
     * @return Entity
     * @throws Exception\NotFoundException
     */
    public function fetchByRegNo($regNo)
    {
        $dqb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($dqb);
        $dqb->where($dqb->expr()->eq($this->alias . '.regNo', ':regNo'))
            ->setParameter('regNo', $regNo);

        $results = $dqb->getQuery()->getResult();

        if (empty($results)) {
            throw new NotFoundException('Resource not found');
        }

        return $results[0];
    }

    /**
     *
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        /** @var ListQueryObject $query */
        if (method_exists($query, 'getLicId') && !empty($query->getLicId())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.licId', ':licId'))
                ->setParameter('licId', $query->getLicId());
        }

        if (method_exists($query, 'getBusRegStatus') && !empty($query->getBusRegStatus())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.busRegStatus', ':busRegStatus'))
                ->setParameter('busRegStatus', $query->getBusRegStatus());
        }

        // apply filter by organisation OR local authority (if set)
        if (method_exists($query, 'getOrganisationId') && !empty($query->getOrganisationId())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.organisationId', ':organisationId'))
                ->setParameter('organisationId', $query->getOrganisationId());
        } elseif (method_exists($query, 'getLocalAuthorityId') && !empty($query->getLocalAuthorityId())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.localAuthorityId', ':localAuthorityId'))
                ->setParameter('localAuthorityId', $query->getLocalAuthorityId());
        }

        // this is required for filtering from BusReg home page
        if (method_exists($query, 'getStatus') && !empty($query->getStatus())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.busRegStatus', ':status'))
                ->setParameter('status', $query->getStatus());
        }

        // OLCS-14215 - need to group by id
        // otherwise we can get multiple rows per id which dehydrates to one object
        $qb->groupBy($this->alias . '.id');
    }

    /**
     * Get Active Bus Regs
     *
     * @param int|\Dvsa\Olcs\Api\Entity\Licence\Licence $licence
     *
     * @return array
     */
    public function fetchActiveByLicence($licence)
    {
        $activeStatuses = [
            BusReg::STATUS_NEW,
            BusReg::STATUS_VAR,
            BusReg::STATUS_REGISTERED,
            BusReg::STATUS_CANCEL,
        ];

        $dqb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($dqb);
        $dqb->where($dqb->expr()->eq($this->alias . '.licId', ':licence'))
            ->setParameter('licence', $licence);
        $dqb->andWhere($dqb->expr()->in($this->alias . '.busRegStatus', ':activeStatuses'))
            ->setParameter('activeStatuses', $activeStatuses);

        return $dqb->getQuery()->getResult();
    }

    /**
     * Fetch a distinct list of record columns based on a context passed in query.
     * For example context = 'organisation' returns all unique organisation IDs and names
     *
     * @param   null            $organisationId
     * @param   null            $localAuthorityId
     * @return array
     */
    public function fetchDistinctList(
        QueryInterface $query,
        $organisationId = null,
        $localAuthorityId = null
    ) {
        $qb = $this->createQueryBuilder();

        // apply filter by organisation OR local authority (if set)
        // organisationId is determined by the logged in user and sent from the query handler and not from the query
        if (!empty($organisationId)) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.organisationId', ':organisationId'))
                ->setParameter('organisationId', $organisationId);
        } elseif (!empty($localAuthorityId)) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.localAuthorityId', ':localAuthorityId'))
                ->setParameter('localAuthorityId', $localAuthorityId);
        }

        switch ($query->getContext()) {
            case 'licence':
                $qb->distinct()
                ->select([$this->alias . '.licId', $this->alias . '.licNo']);
                break;
            case 'organisation':
                $qb->distinct()
                    ->select([$this->alias . '.organisationId', $this->alias . '.organisationName']);
                break;
            case 'busRegStatus':
                $qb->distinct()
                    ->select([$this->alias . '.busRegStatus', $this->alias . '.busRegStatusDesc']);
                break;
        }

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
