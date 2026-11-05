<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;

class NotifyMessage extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * True if an SMS (not cancelled) was already requested for this recipient on the given London calendar day.
     */
    public function hasSmsForRecipientOn(string $recipient, \DateTimeImmutable $londonDay): bool
    {
        $london = new \DateTimeZone('Europe/London');
        $utc = new \DateTimeZone('UTC');

        // requested_on is written by Doctrine in PHP's default zone (UTC), so compare in UTC.
        $start = $londonDay->setTimezone($london)->setTime(0, 0);
        $end = $start->modify('+1 day');

        $qb = $this->createQueryBuilder();
        $qb->select('COUNT(' . $this->alias . '.id)')
            ->andWhere($qb->expr()->eq($this->alias . '.channel', ':channel'))
            ->andWhere($qb->expr()->eq($this->alias . '.recipient', ':recipient'))
            ->andWhere($qb->expr()->neq($this->alias . '.status', ':cancelled'))
            ->andWhere($qb->expr()->gte($this->alias . '.requestedOn', ':dayStart'))
            ->andWhere($qb->expr()->lt($this->alias . '.requestedOn', ':dayEnd'))
            ->setParameter('channel', Entity::CHANNEL_SMS)
            ->setParameter('recipient', $recipient)
            ->setParameter('cancelled', Entity::STATUS_CANCELLED)
            ->setParameter('dayStart', \DateTime::createFromInterface($start->setTimezone($utc)))
            ->setParameter('dayEnd', \DateTime::createFromInterface($end->setTimezone($utc)));

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
