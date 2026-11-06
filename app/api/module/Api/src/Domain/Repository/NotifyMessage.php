<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;

class NotifyMessage extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * True if an SMS (not cancelled) was already requested for this recipient in [start, end).
     */
    /** Has this number had this kind of text in the period? Each kind has its own limit. */
    public function hasSmsForRecipientBetween(string $recipient, string $templateName, \DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        // requested_on is written by Doctrine in PHP's default zone (UTC), so compare in UTC.
        $utc = new \DateTimeZone('UTC');

        $qb = $this->createQueryBuilder();
        $qb->select('COUNT(' . $this->alias . '.id)')
            ->andWhere($qb->expr()->eq($this->alias . '.channel', ':channel'))
            ->andWhere($qb->expr()->eq($this->alias . '.recipient', ':recipient'))
            ->andWhere($qb->expr()->eq($this->alias . '.templateName', ':templateName'))
            ->andWhere($qb->expr()->neq($this->alias . '.status', ':cancelled'))
            ->andWhere($qb->expr()->gte($this->alias . '.requestedOn', ':start'))
            ->andWhere($qb->expr()->lt($this->alias . '.requestedOn', ':end'))
            ->setParameter('channel', Entity::CHANNEL_SMS)
            ->setParameter('recipient', $recipient)
            ->setParameter('templateName', $templateName)
            ->setParameter('cancelled', Entity::STATUS_CANCELLED)
            ->setParameter('start', \DateTime::createFromInterface($start)->setTimezone($utc))
            ->setParameter('end', \DateTime::createFromInterface($end)->setTimezone($utc));

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
