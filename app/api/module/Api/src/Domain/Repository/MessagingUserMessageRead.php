<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead as Entity;

class MessagingUserMessageRead extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function fetchByMessageIdAndUserId(int $messageId, int $userId): Entity
    {
        return ($this->createQueryBuilder())
            ->where($this->alias . '.user = :user_id')
            ->setParameter('user_id', $userId)
            ->andWhere($this->alias . '.messagingMessage = :message_id')
            ->setParameter('message_id', $messageId)
            ->getQuery()
            ->getSingleResult();
    }
}
