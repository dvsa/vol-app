<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity;

/**
 * Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Task extends AbstractRepository
{
    protected $entity = Entity\Task\Task::class;

    /**
     * Fetch a list for an irfo organisation
     *
     * @param int|\Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation Organisation
     *
     * @return array
     */
    public function fetchByIrfoOrganisation($organisation)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.irfoOrganisation', ':organisaion'))
            ->setParameter('organisaion', $organisation);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch a list for a Transport Manager
     *
     * @param int|Entity\Tm\TransportManager $transportManager Transport Manager
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
     * Fetch a list for a user
     *
     * @param int|Entity\User\User $user User
     * @param bool                 $open Only get tasks that are open
     *
     * @return array
     */
    public function fetchByUser($user, $open = false)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.assignedToUser', ':user'))
            ->setParameter('user', $user);

        if ($open) {
            $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.isClosed', ':isClosed'));
            $doctrineQb->setParameter('isClosed', 'N');
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch a single task record belonging to Case and Transport Manager, filtered by subcategory
     *
     * @param int|Entity\Cases\Cases         $case             Case
     * @param int|Entity\Tm\TransportManager $transportManager Transport Manager
     * @param string                         $subCategory      Sub Category
     *
     * @return mixed
     */
    public function fetchForTmCaseDecision(
        $case,
        $transportManager,
        $subCategory = ''
    ) {
        $category = CategoryEntity::CATEGORY_TRANSPORT_MANAGER;

        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.transportManager', ':transportManager'))
            ->setParameter('transportManager', $transportManager);
        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.case', ':case'))
            ->setParameter('case', $case);
        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.category', ':category'))
            ->setParameter('category', $category);

        if (!empty($subCategory)) {
            $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.subCategory', ':subCategory'))
                ->setParameter('subCategory', $subCategory);
        }

        return $doctrineQb->getQuery()->getSingleResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Fetch a single task record belonging to Submission
     *
     * @param int|Entity\Submission\Submission $submission Submission
     *
     * @return mixed
     */
    public function fetchAssignedToSubmission($submission)
    {
        $category = CategoryEntity::CATEGORY_SUBMISSION;
        $subCategory = Entity\Task\Task::SUBCATEGORY_SUBMISSION_ASSIGNMENT;

        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.submission', ':submission'))
            ->setParameter('submission', $submission);
        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.category', ':category'))
            ->setParameter('category', $category);
        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.subCategory', ':subCategory'))
            ->setParameter('subCategory', $subCategory);
        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.isClosed', 0));

        return $doctrineQb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Flag applicable tasks as urgent
     *
     * @return int Number of tasks updated
     */
    public function flagUrgentsTasks()
    {
        /** @var \Doctrine\DBAL\Result $stmt */
        $stmt = $this->getDbQueryManager()
            ->get('Task\FlagUrgentTasks')
            ->execute();

        return (int) $stmt->fetchOne();
    }

    /**
     * Get Team object
     *
     * @param int $teamId Team Id
     * @param int $userId User Id
     *
     * @return Entity\User\Team|null
     */
    public function getTeamReference($teamId, $userId)
    {
        if ($teamId > 0) {
            return $this->getReference(Entity\User\Team::class, $teamId);
        }

        if ($userId > 0) {
            /** @var Entity\User\User $user */
            $user = $this->getReference(Entity\User\User::class, $userId);

            if ($user !== null) {
                return $user->getTeam();
            }
        }

        return null;
    }

    /**
     * Fetch task by application id and description
     *
     * @param int    $applicationId application id
     * @param string $description   description
     * @param bool   $isClosed      is closed
     *
     * @return array
     */
    public function fetchByAppIdAndDescription($applicationId, $description, $isClosed = false)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.application', ':application'))
            ->setParameter('application', $applicationId);

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.description', ':description'))
            ->setParameter('description', $description);

        $isClosedFlag = $isClosed ? 'Y' : 'N';
        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.isClosed', ':isClosed'))
            ->setParameter('isClosed', $isClosedFlag);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch opened tasks for licences
     *
     * @param array  $licenceIds    licence ids
     * @param int    $categoryId    category id
     * @param int    $subCategoryId sub category id
     * @param string $description   description
     *
     * @return array
     */
    public function fetchOpenedTasksForLicences($licenceIds, $categoryId, $subCategoryId, $description)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('licence', 'l');

        $qb
            ->andWhere($qb->expr()->in($this->alias . '.licence', ':licenceIds'))
            ->setParameter('licenceIds', $licenceIds)
            ->andWhere($qb->expr()->eq($this->alias . '.description', ':description'))
            ->setParameter('description', $description)
            ->andWhere($qb->expr()->eq($this->alias . '.isClosed', ':isClosed'))
            ->setParameter('isClosed', 0)
            ->andWhere($qb->expr()->eq($this->alias . '.category', ':categoryId'))
            ->setParameter('categoryId', $categoryId)
            ->andWhere($qb->expr()->eq($this->alias . '.subCategory', ':subCategoryId'))
            ->setParameter('subCategoryId', $subCategoryId);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    public function fetchOpenTasksForSurrender(int $surrenderId)
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.surrender', ':surrenderId'))
            ->setParameter('surrenderId', $surrenderId)
            ->andWhere($qb->expr()->eq($this->alias . '.isClosed', ':isClosed'))
            ->setParameter('isClosed', 0);

        return $qb->getQuery()->getResult();
    }
}
