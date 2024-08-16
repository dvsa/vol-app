<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicence as CommunityLicDTO;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

class CommunityLic extends AbstractRepository
{
    protected $entity = CommunityLicEntity::class;

    /**
     * Apply list filters
     *
     * @param QueryBuilder    $qb    query builder
     * @param CommunityLicDTO $query query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getStatuses() !== null) {
            $statuses = explode(',', $query->getStatuses());
            $conditions = [];
            for ($i = 0; $i < count($statuses); $i++) {
                $conditions[] = $this->alias . '.status = :status' . $i;
            }
            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
            for ($i = 0; $i < count($statuses); $i++) {
                $qb->setParameter('status' . $i, $statuses[$i]);
            }
        }
        if ($query->getLicence() !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'));
            $qb->setParameter('licence', $query->getLicence());
        }
    }

    /**
     * Fetch office copy
     *
     * @param int $licenceId licence idd
     *
     * @return mixed
     */
    public function fetchOfficeCopy($licenceId)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->andWhere($qb->expr()->eq($this->alias . '.issueNo', ':issueNo'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq($this->alias . '.status', ':pending'),
                    $qb->expr()->eq($this->alias . '.status', ':active'),
                    $qb->expr()->eq($this->alias . '.status', ':withdrawn'),
                    $qb->expr()->eq($this->alias . '.status', ':suspended')
                )
            )
            ->setParameter('licence', $licenceId)
            ->setParameter('issueNo', 0)
            ->setParameter('pending', CommunityLicEntity::STATUS_PENDING)
            ->setParameter('active', CommunityLicEntity::STATUS_ACTIVE)
            ->setParameter('withdrawn', CommunityLicEntity::STATUS_WITHDRAWN)
            ->setParameter('suspended', CommunityLicEntity::STATUS_SUSPENDED);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Fetch valid licences
     *
     * @param int $licence licence id
     *
     * @return mixed
     */
    public function fetchValidLicences($licence)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->neq($this->alias . '.issueNo', ':issueNo'))
            ->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq($this->alias . '.status', ':pending'),
                    $qb->expr()->eq($this->alias . '.status', ':active'),
                    $qb->expr()->eq($this->alias . '.status', ':withdrawn'),
                    $qb->expr()->eq($this->alias . '.status', ':suspended')
                )
            )
            ->setParameter('licence', $licence)
            ->setParameter('issueNo', 0)
            ->setParameter('pending', CommunityLicEntity::STATUS_PENDING)
            ->setParameter('active', CommunityLicEntity::STATUS_ACTIVE)
            ->setParameter('withdrawn', CommunityLicEntity::STATUS_WITHDRAWN)
            ->setParameter('suspended', CommunityLicEntity::STATUS_SUSPENDED)
            ->orderBy($this->alias . '.issueNo', 'ASC');
        return $qb->getQuery()->execute();
    }

    /**
     * Fetch licences by ids
     *
     * @param array $ids community licence ids
     *
     * @return array
     */
    public function fetchLicencesByIds($ids)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->in($this->alias . '.id', ':ids'));
        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->execute();
    }

    /**
     * Expire all for licence
     *
     * @param int     $licenceId licence id
     * @param RefData $status    status
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function expireAllForLicence($licenceId, $status = null)
    {
        $params = ['licence' => $licenceId];

        if ($status !== null) {
            $params['status'] = $status;
        }

        $this->getDbQueryManager()->get('CommunityLicence\ExpireAllForLicence')->execute($params);
    }

    /**
     * Fetch licences for suspension
     *
     * @param \DateTime $date date
     *
     * @return ArrayCollection
     */
    public function fetchForSuspension($date)
    {
        $qb = $this->createQueryBuilder();
        $qb->innerJoin('m.communityLicSuspensions', 's')
            ->innerJoin('s.communityLicSuspensionReasons', 'sr')
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':status'))
            ->andWhere($qb->expr()->lte('s.startDate', ':startDate'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.endDate'),
                    $qb->expr()->gt('s.endDate', ':endDate')
                )
            )
            ->setParameter('status', CommunityLicEntity::STATUS_ACTIVE)
            ->setParameter('startDate', $date)
            ->setParameter('endDate', $date);

        return $qb->getQuery()->execute();
    }

    /**
     * Fetch licences for activation
     *
     * @param \DateTime $date date
     *
     * @return ArrayCollection
     */
    public function fetchForActivation($date)
    {
        $qb = $this->createQueryBuilder();
        $qb->innerJoin('m.communityLicSuspensions', 's')
            ->innerJoin('s.communityLicSuspensionReasons', 'sr')
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':status'))
            ->andWhere($qb->expr()->lte('s.endDate', ':endDate'))
            ->setParameter('status', CommunityLicEntity::STATUS_SUSPENDED)
            ->setParameter('endDate', $date);

        return $qb->getQuery()->execute();
    }

    /**
     * Counts the number of active community licences that relate to a licence which is identified by a given id.
     *
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countActiveByLicenceId(int $licenceId): int
    {
        $qb = $this->createQueryBuilder();
        $qb->select(new Func('COUNT', [$this->getColumnIdentifier('id')]));
        $qb->andWhere(new Comparison($this->getColumnIdentifier('issueNo'), Comparison::NEQ, ':issueNo'));
        $qb->andWhere(new Comparison($this->getColumnIdentifier('licence'), Comparison::EQ, ':licence'));
        $qb->andWhere(new Comparison($this->getColumnIdentifier('status'), Comparison::EQ, ':status'));
        $qb->setParameters(['licence' => $licenceId, 'issueNo' => 0, 'status' => CommunityLicEntity::STATUS_ACTIVE]);
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
