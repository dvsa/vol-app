<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory as EventHistoryEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Processing\History as HistoryDTO;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * EventHistory
 */
class EventHistory extends AbstractRepository
{
    protected $entity = EventHistoryEntity::class;

    protected $fieldsToExclude = [
        'hist_id',
        'hist_timestamp',
        'hist_transaction_type',
        'hist_db_user',
        'created_by',
        'last_modified_by',
        'created_on',
        'last_modified_on',
        'version',
        'olbs_key',
        'olbs_type'
    ];

    /**
     * Apply list filters
     *
     * @param QueryBuilder $qb    Query builder
     * @param HistoryDTO   $query Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getCase() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.case', ':caseId'));
            $qb->setParameter('caseId', $query->getCase());
        }

        if ($query->getLicence() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
            $qb->setParameter('licenceId', $query->getLicence());
        }

        if ($query->getOrganisation() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.organisation', ':organisationId'));
            $qb->setParameter('organisationId', $query->getOrganisation());
        }

        if ($query->getTransportManager() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.transportManager', ':transportManagerId'));
            $qb->setParameter('transportManagerId', $query->getTransportManager());
        }

        if ($query->getUser() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.user', ':userId'));
            $qb->setParameter('userId', $query->getUser());
        }

        if ($query->getApplication() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.application', ':applicationId'));
            $qb->setParameter('applicationId', $query->getApplication());
        }

        if ($query->getIrhpApplication() !== null) {
            $qb->orWhere($qb->expr()->eq($this->alias . '.irhpApplication', ':irhpApplicationId'));
            $qb->setParameter('irhpApplicationId', $query->getIrhpApplication());
        }

        $this->getQueryBuilder()->modifyQuery($qb)->with('eventHistoryType')->withUser();
    }

    /**
     * Apply list join
     *
     * @param QueryBuilder $qb Query builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('case')
            ->with('licence')
            ->with('application')
            ->with('organisation')
            ->with('transportManager')
            ->with('busReg')
            ->with('irhpApplication');
    }

    /**
     * Fetch a list for an organisation
     *
     * @param int|\Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation Organisation
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
     * @param int|\Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager Transport Manager
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
     * Fetch a list for an account
     *
     * @param int|\Dvsa\Olcs\Api\Entity\User\User                     $user             User
     * @param int|\Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType $eventHistoryType EventHistoryType
     * @param string                                                  $sort             Sort
     * @param string                                                  $order            Order
     * @param int                                                     $limit            Limit
     *
     * @return array
     */
    public function fetchByAccount($user, $eventHistoryType = null, $sort = null, $order = null, $limit = null)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.account', ':account'))
            ->setParameter('account', $user);

        if ($eventHistoryType !== null) {
            $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.eventHistoryType', ':eventHistoryType'))
                ->setParameter('eventHistoryType', $eventHistoryType);
        }

        if ($sort !== null) {
            $doctrineQb->orderBy($this->alias . '.' . $sort, $order);
        }

        if ($limit !== null) {
            $doctrineQb->setMaxResults($limit);
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch event history details
     *
     * @param int    $id      Id
     * @param int    $version Version
     * @param string $table   Table
     *
     * @return array
     */
    public function fetchEventHistoryDetails($id, $version, $table)
    {
        $eventDetailsQuery = $this->getDbQueryManager()->get('EventHistory\GetEventHistoryDetails');
        $eventDetailsQuery->setHistoryTable($table);
        $stmt = $eventDetailsQuery->execute(['id' => $id, 'version' => [$version, $version - 1]]);
        $eventHistory = $stmt->fetchAll();
        $returnValues = [];

        if (count($eventHistory)) {
            $cleanValues = [];
            foreach ($eventHistory as $hist) {
                foreach ($this->fieldsToExclude as $field) {
                    if (array_key_exists($field, $hist)) {
                        unset($hist[$field]);
                    }
                }
                $cleanValues[] = $hist;
            }
            $keys = array_keys($cleanValues[0]);
            for ($i = 0; $i < count($keys); $i++) {
                if (isset($cleanValues[1]) && $cleanValues[0][$keys[$i]] === $cleanValues[1][$keys[$i]]) {
                    continue;
                }
                $element['newValue'] = $cleanValues[0][$keys[$i]];
                $element['oldValue'] = $cleanValues[1][$keys[$i]] ?? '';
                $element['name'] = $keys[$i];
                $returnValues[] = $element;
            }

            $oldValueDeletedDate = isset($cleanValues[1]['deleted_date'])
                ? $cleanValues[1]['deleted_date']
                : null;

            $newValueDeletedDate = isset($cleanValues[0]['deleted_date'])
                ? $cleanValues[0]['deleted_date']
                : null;

            if ($newValueDeletedDate !== null) {
                $returnValues = [];

                foreach ($cleanValues[1] as $key => $oldValue) {
                    $returnValues[] = [
                        'name' => $key,
                        'oldValue' => $oldValue,
                        'newValue' => '',
                    ];
                }
            }
        }

        return $returnValues;
    }

    /**
     * Fetch a list for a task
     *
     * @param int $taskId task
     *
     * @return array
     */
    public function fetchByTask($taskId)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.task', ':task'))
            ->setParameter('task', $taskId);

        $this->getQueryBuilder()->modifyQuery($doctrineQb)
            ->with('eventHistoryType', 'eht')
            ->with('user', 'u')
            ->with('u.contactDetails', 'cd')
            ->with('cd.person', 'p');

        return $doctrineQb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    public function fetchPreviousLicenceStatus($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $qb->select('eht.id')
            ->innerJoin($this->alias . '.eventHistoryType', 'eht')
            ->innerJoin($this->alias . '.licence', 'l')
            ->where($qb->expr()->in('eht.id', [7, 31, 75]))
            ->andWhere($qb->expr()->eq('l.id', ':licenceId'))
            ->setParameter('licenceId', $licenceId)
            ->orderBy($this->alias . '.eventDatetime', 'DESC')
            ->setMaxResults(1);

        try {
            $eventType = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException) {
            $eventType = 75;
        } catch (\Exception $exception) {
            throw $exception;
        }

        switch ($eventType) {
            case 7:
                $status = Licence::LICENCE_STATUS_CURTAILED;
                break;
            case 31:
                $status = Licence::LICENCE_STATUS_SUSPENDED;
                break;
            case 75:
                $status = Licence::LICENCE_STATUS_VALID;
                break;
        }

        return [
            'status' => $status
        ];
    }
}
