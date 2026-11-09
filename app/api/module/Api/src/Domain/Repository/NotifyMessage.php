<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Service\Sms\SmsNotQueued;

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

    /**
     * Insert a text and the queue item that sends it, inside a savepoint.
     *
     * This runs inside the letter's transaction, and the letter matters more than the text. Plain DBAL is used rather
     * than the EntityManager, because a failed flush closes the EntityManager for the rest of the request, and a
     * savepoint can't undo that. So on failure this rolls back to the savepoint and throws SmsNotQueued, and the
     * letter carries on.
     *
     * A deadlock rolls back the whole transaction, letter included, so that error is passed on unchanged, as is any
     * failure to roll back to the savepoint.
     *
     * @param string                  $sendCommandClass the command the queue item runs, given the message id
     * @param \DateTimeInterface|null $processAfter     null to send straight away
     * @param int|null                $userId           written to created_by and last_modified_by
     *
     * @return int the notify_message id
     *
     * @throws SmsNotQueued nothing was written, and the transaction can still be committed
     */
    public function insertQueuedSms(Entity $message, string $sendCommandClass, ?\DateTimeInterface $processAfter, ?int $userId): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $now = self::utc(new \DateTimeImmutable());

        $connection->beginTransaction();
        try {
            $connection->insert('notify_message', [
                'channel' => $message->getChannel()->getId(),
                'status' => $message->getStatus()->getId(),
                'recipient' => $message->getRecipient(),
                'template_name' => $message->getTemplateName(),
                'description' => $message->getDescription(),
                'organisation_id' => $message->getOrganisation()?->getId(),
                'licence_id' => $message->getLicence()?->getId(),
                'application_id' => $message->getApplication()?->getId(),
                'bus_reg_id' => $message->getBusReg()?->getId(),
                'case_id' => $message->getCase()?->getId(),
                'transport_manager_id' => $message->getTransportManager()?->getId(),
                'irhp_application_id' => $message->getIrhpApplication()?->getId(),
                'document_id' => $message->getDocument()?->getId(),
                'correspondence_inbox_id' => $message->getCorrespondenceInbox()?->getId(),
                'user_id' => $message->getUser()?->getId(),
                'attempts' => 0,
                'requested_on' => self::utc($message->getRequestedOn(true)),
                'created_by' => $userId,
                'last_modified_by' => $userId,
                'created_on' => $now,
                'version' => 1,
            ]);
            $messageId = (int) $connection->lastInsertId();

            $connection->insert('queue', [
                'type' => Queue::TYPE_SMS,
                'status' => Queue::STATUS_QUEUED,
                'entity_id' => $messageId,
                'options' => json_encode(
                    ['commandClass' => $sendCommandClass, 'commandData' => ['id' => $messageId]],
                    JSON_THROW_ON_ERROR
                ),
                'process_after_date' => $processAfter === null ? null : self::utc($processAfter),
                'attempts' => 0,
                'created_by' => $userId,
                'last_modified_by' => $userId,
                'created_on' => $now,
                'version' => 1,
            ]);

            $connection->commit();
        } catch (\Throwable $e) {
            try {
                $connection->rollBack();
            } catch (\Throwable) {
                // the savepoint has gone, so has the transaction around it
                throw $e;
            }

            if ($e instanceof DeadlockException) {
                throw $e;
            }

            throw SmsNotQueued::because($e);
        }

        return $messageId;
    }

    /** Dates are stored in UTC, as the queries here read them. */
    private static function utc(\DateTimeInterface $date): string
    {
        return \DateTimeImmutable::createFromInterface($date)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
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
