<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as Entity;
use Dvsa\Olcs\Api\Domain\Repository\Query\Bus\Expire as ExpireQuery;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query\Expr\Join;

class Bus extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch the default record by it's id
     *
     * @param QryCmd   $query       the query
     * @param int      $hydrateMode doctrine hydrate mode
     * @param null|int $version     the version
     *
     * @return Entity
     * @throws Exception\NotFoundException
     * @throws Exception\VersionConflictException
     */
    #[\Override]
    public function fetchUsingId(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('busNoticePeriod')
            ->with('busServiceTypes')
            ->with('trafficAreas')
            ->with('localAuthoritys')
            ->with('subsidised')
            ->with('otherServices')
            ->byId($query->getId());

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * fetch with txc inbox list for organisation
     *
     * @param QueryInterface $query          the query
     * @param int            $organisationId organisation id
     * @param int            $hydrateMode    doctrine hydrate mode
     *
     * @return Entity
     * @throws Exception\NotFoundException
     */
    public function fetchWithTxcInboxListForOrganisation($query, $organisationId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->byId($query->getId())
            ->withRefdata();

        $qb->addSelect('t');

        $qb->leftJoin(
            $this->alias . '.txcInboxs',
            't',
            Join::WITH,
            't.localAuthority IS NULL AND t.organisation = :organisation'
        )->setParameter('organisation', $organisationId);

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        return $results[0];
    }

    /**
     * Fetch a list of unread docs filtered by local authority, submission type and status for a given bus reg id
     *
     * @param QryCmd $query            query
     * @param int    $localAuthorityId local authority id
     * @param int    $hydrateMode      doctrine hydrate mode
     *
     * @throws Exception\NotFoundException
     * @return Entity
     */
    public function fetchWithTxcInboxListForLocalAuthority(
        $query,
        $localAuthorityId,
        $hydrateMode = Query::HYDRATE_OBJECT
    ) {
        /* @var \Doctrine\Orm\QueryBuilder $qb */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->byId($query->getId())
            ->withRefdata();

        $qb->addSelect('t');

        if (empty($localAuthorityId)) {
            $qb->leftJoin($this->alias . '.txcInboxs', 't', Join::WITH, $qb->expr()->isNull('t.localAuthority'));
        } else {
            $qb->leftJoin(
                $this->alias . '.txcInboxs',
                't',
                Join::WITH,
                $qb->expr()->eq('t.localAuthority', ':localAuthority')
            )->setParameter('localAuthority', $localAuthorityId);
        }

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        return $results[0];
    }

    /**
     * Applies filters
     *
     * @param QueryBuilder   $qb    doctrine query builder
     * @param QueryInterface $query query being run
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.routeNo', ':byRouteNo'))
            ->setParameter('byRouteNo', $query->getRouteNo());

        if (method_exists($query, 'getVariationNo')) {
            $qb->andWhere($qb->expr()->lt($this->alias . '.variationNo', ':byVariationNo'))
                ->setParameter('byVariationNo', $query->getVariationNo());
        }

        if (method_exists($query, 'getLicenceId')) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':byLicence'))
                ->setParameter('byLicence', $query->getLicenceId());
        }

        //array of allowable bus reg statuses (optional parameter, if empty return everything)
        if (method_exists($query, 'getBusRegStatus') && !empty($query->getBusRegStatus())) {
            $qb->andWhere($qb->expr()->in($this->alias . '.status', ':byStatus'))
                ->setParameter('byStatus', $query->getBusRegStatus());
        }
    }

    /**
     * Applies list joins
     *
     * @param QueryBuilder $qb query builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('busNoticePeriod')
            ->with('status');
    }

    /**
     * expire all bus registrations that have reached their end date
     */
    public function expireRegistrations(): int
    {
        return $this->getDbQueryManager()->get(ExpireQuery::class)->execute([])->rowCount();
    }
}
