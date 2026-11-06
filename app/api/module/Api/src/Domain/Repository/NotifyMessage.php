<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;

class NotifyMessage extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * True if this kind of text (not cancelled) was requested or sent for this number in [start, end).
     * Each kind of text has its own limit.
     */
    public function hasSmsForRecipientBetween(string $recipient, string $templateName, \DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        $qb = $this->smsForRecipient($recipient, $templateName, $start, $end);

        // a text retried into the next morning was requested the day before, so its send time counts too
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->andX(
                $qb->expr()->gte($this->alias . '.requestedOn', ':start'),
                $qb->expr()->lt($this->alias . '.requestedOn', ':end')
            ),
            $qb->expr()->andX(
                $qb->expr()->gte($this->alias . '.sentOn', ':start'),
                $qb->expr()->lt($this->alias . '.sentOn', ':end')
            )
        ));

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * True if another text of this kind (not cancelled) was sent to this number in [start, end).
     */
    public function hasOtherSmsSentToRecipientBetween(int $exceptId, string $recipient, string $templateName, \DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        $qb = $this->smsForRecipient($recipient, $templateName, $start, $end);
        $qb->andWhere($qb->expr()->neq($this->alias . '.id', ':exceptId'))
            ->andWhere($qb->expr()->gte($this->alias . '.sentOn', ':start'))
            ->andWhere($qb->expr()->lt($this->alias . '.sentOn', ':end'))
            ->setParameter('exceptId', $exceptId);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    private function smsForRecipient(string $recipient, string $templateName, \DateTimeInterface $start, \DateTimeInterface $end): QueryBuilder
    {
        // the dates are written by Doctrine in PHP's default zone (UTC), so compare in UTC.
        $utc = new \DateTimeZone('UTC');

        $qb = $this->createQueryBuilder();
        $qb->select('COUNT(' . $this->alias . '.id)')
            ->andWhere($qb->expr()->eq($this->alias . '.channel', ':channel'))
            ->andWhere($qb->expr()->eq($this->alias . '.recipient', ':recipient'))
            ->andWhere($qb->expr()->eq($this->alias . '.templateName', ':templateName'))
            ->andWhere($qb->expr()->neq($this->alias . '.status', ':cancelled'))
            ->setParameter('channel', Entity::CHANNEL_SMS)
            ->setParameter('recipient', $recipient)
            ->setParameter('templateName', $templateName)
            ->setParameter('cancelled', Entity::STATUS_CANCELLED)
            ->setParameter('start', \DateTime::createFromInterface($start)->setTimezone($utc))
            ->setParameter('end', \DateTime::createFromInterface($end)->setTimezone($utc));

        return $qb;
    }
}
