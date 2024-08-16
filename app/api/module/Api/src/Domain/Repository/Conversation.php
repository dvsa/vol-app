<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation as Entity;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Conversation extends AbstractRepository
{
    protected $entity = Entity::class;

    public function getBaseConversationListQuery(QueryInterface $query): QueryBuilder
    {
        $qb = $this->createDefaultListQuery($query);

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('task', 't1')
            ->with('t1.licence', 'l1')
            ->with('t1.application')
            ->with('t1.category')
            ->with('t1.subCategory')
            ->with('createdBy')
            ->with('lastModifiedBy');
        return $qb;
    }

    public function filterByLicenceId(QueryBuilder $qb, $licenceId): QueryBuilder
    {
        $qb
            ->innerJoin($this->alias . '.task', 't')
            ->andWhere($qb->expr()->isNotNull('t.licence'))
            ->andWhere($qb->expr()->eq('t.licence', ':licence'))
            ->setParameter('licence', $licenceId);

        return $qb;
    }

    public function getByOrganisationId(QueryInterface $query, int $organisationId): QueryBuilder
    {
        $qb = $this->getBaseConversationListQuery($query);

        $qb->innerJoin($this->alias . '.task', 't')
            ->andWhere($qb->expr()->isNotNull('t.licence'))
            ->andWhere($qb->expr()->eq('l1.organisation', ':organisation'))
            ->setParameter('organisation', $organisationId)
            ->addOrderBy($this->alias . '.isClosed', 'ASC');

        return $qb;
    }

    public function applyOrderForListing(QueryBuilder $qb, array $roleNames): QueryBuilder
    {
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery->select('1')
            ->from(MessagingUserMessageRead::class, 'inner_read')
            ->join('inner_read.user', 'inner_user')
            ->join('inner_user.roles', 'inner_role')
            ->where('inner_read.messagingMessage = irmm.id')
            ->andWhere($qb->expr()->in('inner_role.role', ':roleNames'));

        $qb->leftJoin(MessagingMessage::class, 'irmm', Join::WITH, 'irmm.messagingConversation = ' . $this->alias . '.id')
            ->leftJoin('irmm.messagingConversation', 'inner_conversation')
            ->leftJoin('inner_conversation.task', 'inner_task')
            ->addSelect('MAX(irmm.createdOn) AS last_read')
            ->addSelect('CASE WHEN ' . $this->alias . '.isClosed = 0 AND NOT EXISTS (' . $subQuery->getDQL() . ') THEN 1 ELSE 0 END AS has_unread')
            ->groupBy($this->alias . '.id')
            ->addOrderBy($this->alias . '.isClosed', 'ASC')
            ->addOrderBy('has_unread', 'DESC')
            ->addOrderBy('last_read', 'DESC')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq($this->alias . '.isClosed', ':closed'),
                $qb->expr()->exists(
                    $this->getEntityManager()->createQueryBuilder()
                        ->select('1')
                        ->from(MessagingUserMessageRead::class, 'filter_read')
                        ->join('filter_read.user', 'filter_user')
                        ->join('filter_user.roles', 'filter_role')
                        ->where('filter_read.messagingMessage = irmm.id')
                        ->andWhere($qb->expr()->in('filter_role.role', ':roleNames'))
                        ->getDQL()
                )
            ))
            ->setParameter('roleNames', $roleNames)
            ->setParameter('closed', false);

        return $qb;
    }

    public function filterByStatuses(QueryBuilder $qb, array $statuses): QueryBuilder
    {
        $conditions = [];

        if (in_array('open', $statuses)) {
            $conditions[] = $this->alias . '.isClosed = 0';
        }

        if (in_array('closed', $statuses)) {
            $conditions[] = $this->alias . '.isClosed = 1';
        }

        if (count($conditions) > 0) {
            $qb->andWhere($qb->expr()->orX()->addMultiple($conditions));
        }

        return $qb;
    }
}
