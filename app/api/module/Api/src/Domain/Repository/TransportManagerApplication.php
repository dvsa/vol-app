<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity;
use Doctrine\ORM\Query;

/**
 * Transport Manager Application Repository
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerApplication extends AbstractRepository
{
    protected $entity = Entity\Tm\TransportManagerApplication::class;
    protected $alias = 'tma';

    /**
     * Get a list of transport manager application with contact details
     *
     * @param int $applicationId application id
     *
     * @return array TransportManagerApplication entities
     */
    public function fetchWithContactDetailsByApplication($applicationId)
    {
        $dqb = $this->createQueryBuilder();

        $dqb->leftJoin($this->alias . '.transportManager', 'tm')
            ->leftJoin($this->alias . '.tmApplicationStatus', 'tmas')
            ->leftJoin('tm.homeCd', 'hcd')
            ->leftJoin('hcd.person', 'hp')
            ->select($this->alias . '.id')
            ->addSelect($this->alias . '.action')
            ->addSelect('tm.id as tmid')
            ->addSelect('tmas.id as tmasid, tmas.description as tmasdesc')
            ->addSelect('hcd.emailAddress')
            ->addSelect('hp.birthDate, hp.forename, hp.familyName');

        $dqb->andWhere($dqb->expr()->eq($this->alias . '.application', ':applicationId'))
            ->setParameter('applicationId', $applicationId);

        return $dqb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Fetch details
     *
     * @param int $tmaId Transport Manager Application ID
     *
     * @return Entity\Tm\TransportManagerApplication
     * @throws \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     */
    public function fetchDetails($tmaId)
    {
        $dqb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($dqb)
            ->withRefdata()
            ->with($this->alias . '.application', 'a')
            ->with($this->alias . '.otherLicences', 'ol')
            ->with('ol.role')
            ->with('a.goodsOrPsv', 'gop')
            ->with('a.licence')
            ->with('a.status')
            ->byId($tmaId);

        $this->joinTmContactDetails();

        $results = $dqb->getQuery()->getResult();

        if (empty($results)) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\NotFoundException('Resource not found');
        }

        return $results[0];
    }

    /**
     * Join Trasport Manager, Contact Details and Person entities to the query
     *
     * @return void
     */
    protected function joinTmContactDetails()
    {
        $this->getQueryBuilder()->with($this->alias . '.transportManager', 'tm')
            ->with('tm.homeCd', 'hcd')
            ->with('hcd.address', 'hadd')
            ->with('hadd.countryCode')
            ->with('hcd.person', 'hp')
            ->with('tm.workCd', 'wcd')
            ->with('wcd.address', 'wadd')
            ->with('wadd.countryCode');
    }

    /**
     * Apply filters
     *
     * @param \Doctrine\ORM\QueryBuilder               $qb    Doctrine Query Builder
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $query Http Query Builder
     *
     * @return void
     */
    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
    {
        // if user param exists then add where clauses
        if ($query->getUser()) {
            $qb->join('tma.transportManager', 'tm');
            $qb->join('tm.users', 'u');
            $qb->andWhere($qb->expr()->eq('u.id', ':user'))
                ->setParameter('user', $query->getUser());
        }

        if ($query->getApplication()) {
            $qb->andWhere($qb->expr()->eq('tma.application', ':application'))
                ->setParameter('application', $query->getApplication());
        }

        if ($query->getTransportManager()) {
            $qb->andWhere($qb->expr()->eq('tma.transportManager', ':transportManager'))
                ->setParameter('transportManager', $query->getTransportManager());
        }

        if ($query->getAppStatuses()) {
            $qb->andWhere($qb->expr()->in('a.status', ':appStatuses'))
                ->setParameter('appStatuses', $query->getAppStatuses());
        }

        if ($query->getFilterByOrgUser() && $query->getUser()) {
            $qb->join('l.organisation', 'o');
            $qb->join('o.organisationUsers', 'ou');
            $qb->join('ou.user', 'ouu');
            $qb->andWhere($qb->expr()->eq('ouu.id', ':orgUsersUser'))
                ->setParameter('orgUsersUser', $query->getUser());
        }
    }

    /**
     * Add joins
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Doctrine query builder
     *
     * @return void
     */
    protected function applyListJoins(\Doctrine\ORM\QueryBuilder $qb)
    {
        $this->getQueryBuilder()
            ->with('application', 'a')
            ->with('a.licence', 'l');
    }

    /**
     * Fetch For Transport Manager
     *
     * @param int        $tmId                Transport manager id
     * @param array|null $applicationStatuses Application statuses
     * @param bool       $includeDeleted      Is include deleted
     *
     * @return array
     */
    public function fetchForTransportManager($tmId, array $applicationStatuses = null, $includeDeleted = false)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('tmType', 'tmt')
            ->with('application', 'a')
            ->with('a.licence', 'al')
            ->with('al.organisation', 'alo')
            ->with('a.status', 'ast')
            ->with('transportManager', 'tm')
            ->with('tmApplicationStatus', 'tmast');

        $qb->where($qb->expr()->eq($this->alias . '.transportManager', ':transportManager'));
        $qb->setParameter('transportManager', $tmId);

        if (!$includeDeleted) {
            $qb->andWhere($qb->expr()->neq($this->alias . '.action', ':action'));
            $qb->setParameter('action', 'D');
        }

        if ($applicationStatuses !== null) {
            $qb->andWhere($qb->expr()->in('a.status', $applicationStatuses));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch By Tm And Application
     *
     * @param int  $tmId          Transport manager Id
     * @param int  $applicationId Application Id
     * @param bool $ignoreDeleted Is ignore deleted
     *
     * @return array
     */
    public function fetchByTmAndApplication($tmId, $applicationId, $ignoreDeleted = false)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.transportManager', ':tmId'))
            ->setParameter('tmId', $tmId);
        $qb->andWhere($qb->expr()->eq($this->alias . '.application', ':applicationId'))
            ->setParameter('applicationId', $applicationId);
        if ($ignoreDeleted) {
            $qb->andWhere($qb->expr()->neq($this->alias . '.action', ':action'))
                ->setParameter('action', 'D');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch For Responsibilities
     *
     * @param int $id Tm-App relation id
     *
     * @return mixed
     */
    public function fetchForResponsibilities($id)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('application', 'a')
            ->with('tmType', 'tmty')
            ->with('a.licence', 'al')
            ->with('al.organisation', 'alo')
            ->with('a.status', 'ast')
            ->with('transportManager', 'tm')
            ->with('tm.tmType', 'tmt')
            ->with('tmApplicationStatus', 'tmast')
            ->byId($id);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Fetch statistic data by Application Id
     *
     * @param int $appId Application/Variation Id
     *
     * @return array
     */
    public function fetchStatByAppId($appId)
    {
        $dqb = $this->createQueryBuilder();

        $dqb
            ->select($this->alias . '.id')
            ->groupBy($this->alias . '.application')
            ->andWhere(
                $dqb->expr()->eq($this->alias . '.application', ':applicationId')
            )
            ->setParameter('applicationId', $appId);

        //  add fields to get count Added/Updated/Deleted
        $actions = [
            Entity\Tm\TransportManagerApplication::ACTION_ADD,
            Entity\Tm\TransportManagerApplication::ACTION_UPDATE,
            Entity\Tm\TransportManagerApplication::ACTION_DELETE,
        ];
        foreach ($actions as $action) {
            $dqb->addSelect(
                'SUM(' .
                    'CASE' .
                        ' WHEN ' . $this->alias . '.action = \'' . $action . '\'' .
                        ' THEN 1' .
                        ' ELSE 0' .
                    ' END' .
                ') AS ' . $action
            );
        }

        $result = $dqb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY) ?: [];

        return [
            'action' => $result +
                array_fill_keys($actions, 0),
        ];
    }
}
