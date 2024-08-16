<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteHistoryEntity;
use Dvsa\Olcs\Transfer\Query\Processing\NoteList as NoteDTO;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

class Note extends AbstractRepository
{
    protected $entity = NoteHistoryEntity::class;

    protected $alias = 'n';

    /**
     * @param QueryBuilder $qb
     * @param NoteDTO $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Transfer\Query\Processing\NoteList $query */

        if ($query->getCase() !== null) {
            $qb->orWhere($this->alias . '.case = :caseId');
            $qb->setParameter('caseId', $query->getCase());
        }

        if ($query->getCasesMultiple() !== null && count($query->getCasesMultiple()) > 0) {
            $qb->orWhere($this->alias . '.case IN (:casesMultiple)');
            $qb->setParameter('casesMultiple', $query->getCasesMultiple());
        }

        if ($query->getLicence() !== null) {
            $qb->orWhere($this->alias . '.licence = :licenceId');
            $qb->setParameter('licenceId', $query->getLicence());
        }

        if ($query->getOrganisation() !== null) {
            $qb->orWhere($this->alias . '.organisation = :organisationId');
            $qb->setParameter('organisationId', $query->getOrganisation());
        }

        if ($query->getTransportManager() !== null) {
            $qb->orWhere($this->alias . '.transportManager = :transportManagerId');
            $qb->setParameter('transportManagerId', $query->getTransportManager());
        }

        if ($query->getUser() !== null) {
            $qb->andWhere($this->alias . '.createdBy = :userId');
            $qb->setParameter('userId', $query->getUser());
        }

        if ($query->getApplication() !== null) {
            $qb->orWhere($this->alias . '.application = :applicationId');
            $qb->setParameter('applicationId', $query->getApplication());
        }

        if ($query->getNoteType() !== null) {
            $qb->andWhere($this->alias . '.noteType = :noteTypeId');
            $qb->setParameter('noteTypeId', $query->getNoteType());
        }
        $qb->orderBy($this->alias . '.priority', 'DESC');
        $qb->addOrderBy($this->alias . '.createdOn', 'DESC');

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withCreatedBy()
            ->withBusReg()
            ->withCase()
            ->withApplication()
            ->withIrhpApplication();
    }

    /**
     * Fetch a list for an organisation
     *
     * @param int|Organisation $organisation
     *
     * @return array
     */
    public function fetchByOrganisation($organisation)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.organisation', ':organisaion'))
            ->setParameter('organisaion', $organisation);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch a list for a Transport Manager
     *
     * @param int|\Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager
     *
     * @return array
     */
    public function fetchByTransportManager($transportManager)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.transportManager', ':transportManager'))
            ->setParameter('transportManager', $transportManager);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch the latest note for application / licence with given note type
     *
     * @param int $licence
     * @param int $application
     * @param int $tm
     * @param string $noteType
     *
     * @return array
     */
    public function fetchForOverview($licence = null, $application = null, $tm = null, $noteType = null)
    {
        $qb = $this->createQueryBuilder();

        if ($application !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.application', ':applicationId'));
            $qb->setParameter('applicationId', $application);
        }

        if ($licence !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
            $qb->setParameter('licenceId', $licence);
        }

        if ($tm !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.transportManager', ':tmId'));
            $qb->setParameter('tmId', $tm);
        }

        if ($noteType !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.noteType', ':noteTypeId'));
            $qb->setParameter('noteTypeId', $noteType);
        }

        $qb->orderBy($this->alias . '.priority', 'DESC');
        $qb->addOrderBy($this->alias . '.createdOn', 'DESC');
        $qb->setMaxResults(1);

        $res = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        return count($res) ? $res[0] : [];
    }
}
