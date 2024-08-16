<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Message extends AbstractRepository
{
    protected $entity = Entity::class;

    public function getBaseMessageListQuery(QueryInterface $query): QueryBuilder
    {
        $qb = $this->createDefaultListQuery($query);

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('createdBy')
            ->with('lastModifiedBy')
            ->order('createdOn', 'desc');

        return $qb;
    }

    public function getBaseMessageListWithContentQuery(QueryInterface $query): QueryBuilder
    {
        $qb = $this->getBaseMessageListQuery($query);

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('messagingContent')
            ->withCreatedByWithTeam();

        $qb->leftJoin(
            $this->alias . '.documents',
            'd',
            Query\Expr\Join::WITH,
            $this->alias . '.messagingConversation = d.messagingConversation'
        );
        $qb->addSelect('d');

        return $qb;
    }

    public function filterByConversationId(QueryBuilder $qb, int $conversationId): QueryBuilder
    {
        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.messagingConversation', ':messagingConversation'))
            ->setParameter('messagingConversation', $conversationId);

        return $qb;
    }

    public function addReadersToMessages(QueryBuilder $qb): QueryBuilder
    {
        $qb->leftJoin($this->alias . '.userMessageReads', 'urm')
           ->leftJoin('urm.user', 'urmu')
           ->leftJoin('urmu.contactDetails', 'urmcd')
           ->leftJoin('urmcd.person', 'urmcdp')
           ->leftJoin('urmu.roles', 'urmur')
           ->addSelect('urm, urmcd, urmcdp, urmu, urmur');

        return $qb;
    }

    public function getLastMessageForConversation(int $conversationId): array
    {
        $qb = $this->createQueryBuilder();
        $qb = $this->filterByConversationId($qb, $conversationId);
        $qb->orderBy($this->alias . '.id', 'desc');
        $qb->setMaxResults(1);
        return $qb->getQuery()->getSingleResult(Query::HYDRATE_ARRAY);
    }

    private function getUnreadMessageCountBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder();

        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery
            ->select('1')
            ->from(\Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead::class, 'inner_read')
            ->leftJoin('inner_read.user', 'inner_user')
            ->leftJoin('inner_user.roles', 'inner_role')
            ->where('inner_read.messagingMessage = ' . $this->alias . '.id')
            ->andWhere($qb->expr()->in('inner_role.role', ':roleNames'));

        $qb
            ->leftJoin($this->alias . '.messagingConversation', 'inner_conversation')
            ->leftJoin('inner_conversation.task', 'inner_task')
            ->groupBy($this->alias . '.messagingConversation')
            ->where('inner_conversation.isClosed = 0')
            ->andWhere($qb->expr()->not($qb->expr()->exists($subQuery)));

        return $qb;
    }

    public function getUnreadMessageCountByConversationAndRoles(int $conversationId, array $roleNames): int
    {
        $qb = $this->getUnreadMessageCountBaseQuery();
        $qb->andWhere('inner_conversation.id = :conversationId');

        $qb->setParameter('conversationId', $conversationId);
        $qb->setParameter('roleNames', $roleNames);

        return count($qb->getQuery()->getScalarResult());
    }

    public function getUnreadConversationCountByLicenceAndRoles(int $licenceId, array $roleNames): int
    {
        $qb = $this->getUnreadMessageCountBaseQuery();
        $qb->andWhere('inner_task.licence = :licenceId');

        $qb->setParameter('licenceId', $licenceId);
        $qb->setParameter('roleNames', $roleNames);

        return count($qb->getQuery()->getScalarResult());
    }

    public function getUnreadConversationCountByOrganisationAndRoles(int $organisationId, array $roleNames): int
    {
        $qb = $this->getUnreadMessageCountBaseQuery();

        $qb->leftJoin('inner_task.licence', 'l')
           ->leftJoin('l.organisation', 'o')
           ->andWhere('l.organisation = :organisationId');

        $qb->setParameter('organisationId', $organisationId);
        $qb->setParameter('roleNames', $roleNames);

        return count($qb->getQuery()->getScalarResult());
    }
}
