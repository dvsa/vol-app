<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TransportManagerLicence repository
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class TransportManagerLicence extends AbstractRepository
{
    protected $alias = 'tml';

    protected $entity = Entity\Tm\TransportManagerLicence::class;

    /**
     * Fetch By Licence Id
     *
     * @param int $licenceId Licence Id
     *
     * @return array
     */
    public function fetchForLicence($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
        $qb->setParameter('licenceId', $licenceId);

        return $qb->getQuery()->getResult();
    }

    public function fetchRemovedTmForLicence($licenceId)
    {
        $this->disableSoftDeleteable(
            [
                $this->entity
            ]
        );
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
        $qb->andWhere($qb->expr()->isNotNull($this->alias . '.deletedDate'));
        $qb->andWhere($qb->expr()->isNull($this->alias . '.lastTmLetterDate'));

        $qb->setParameter('licenceId', $licenceId);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get a list of transport manager licences with contact details
     *
     * @param int $licenceId licence id
     *
     * @return array TransportManagerLicence entities
     */
    public function fetchWithContactDetailsByLicence($licenceId)
    {
        $dqb = $this->createQueryBuilder();

        $dqb->andWhere($dqb->expr()->eq($this->alias . '.licence', ':licenceId'))
            ->setParameter('licenceId', $licenceId);

        $dqb->join($this->alias . '.transportManager', 'tm')
            ->join('tm.homeCd', 'hcd')
            ->join('hcd.person', 'p')
            ->select($this->alias . '.id')
            ->addSelect('tm.id as tmid')
            ->addSelect('p.birthDate, p.forename, p.familyName')
            ->addSelect('hcd.emailAddress');

        return $dqb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Fetch for Transport Manager
     *
     * @param int        $tmId            Transport Manager Id
     * @param array|null $licenceStatuses Licence statuses
     *
     * @return array
     */
    public function fetchForTransportManager($tmId, array $licenceStatuses = null)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('tmType', 'tmt')
            ->with('licence', 'l')
            ->with('l.organisation', 'lo')
            ->with('l.status', 'ls')
            ->with('transportManager', 'tm');

        $qb->where($qb->expr()->eq($this->alias . '.transportManager', ':transportManager'));
        $qb->setParameter('transportManager', $tmId);

        if ($licenceStatuses !== null) {
            $qb->andWhere($qb->expr()->in('l.status', $licenceStatuses));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch by TM-Licence relation id
     *
     * @param int $id Transport Manager to Licence relation id
     *
     * @return Entity\Tm\TransportManagerLicence
     */
    public function fetchForResponsibilities($id)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('licence', 'l')
            ->with('l.organisation', 'lo')
            ->with('l.status', 'lst')
            ->with('transportManager', 'tm')
            ->with('tm.tmType', 'tmty')
            ->with('tmType', 'tmt')
            ->byId($id);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Fetch By Tm And Licence
     *
     * @param int $tmId      Transport manager Id
     * @param int $licenceId Licence id
     *
     * @return array
     */
    public function fetchByTmAndLicence($tmId, $licenceId)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.transportManager', ':tmId'))
            ->setParameter('tmId', $tmId);
        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licenceId'))
            ->setParameter('licenceId', $licenceId);

        return $qb->getQuery()->getResult();
    }

    /**
     * Apply Filters for List
     *
     * @param QueryBuilder   $qb    Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getLicence') && $query->getLicence()) {
            $qb->where($qb->expr()->eq('tml.licence', ':licence'))
                ->setParameter('licence', $query->getLicence());
        }

        if (method_exists($query, 'getTransportManager') && $query->getTransportManager()) {
            $qb->where($qb->expr()->eq('tml.transportManager', ':transportManager'))
                ->setParameter('transportManager', $query->getTransportManager());
        }
    }

    /**
     * Fetch By Licence
     *
     * @param int $licenceId Licence Id
     *
     * @return array
     */
    public function fetchByLicence($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('licence', 'l')
            ->with('l.applications', 'la')
            ->with('la.licenceType')
            ->with('transportManager', 'tm');

        $qb->where($qb->expr()->eq('tml.licence', ':licence'))
            ->setParameter('licence', $licenceId);

        return $qb->getQuery()->getResult();
    }
}
