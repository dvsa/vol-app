<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractException as DriverException;
use Doctrine\DBAL\Exception\DeadlockException;
use Dvsa\Olcs\Api\Domain\Command\Sms\SendSms;
use Dvsa\Olcs\Api\Domain\Repository\NotifyMessage as Repo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Sms\SmsNotQueued;
use Mockery as m;

final class NotifyMessageTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testHasSmsForRecipientBetweenBuildsRangeQueryInUtc(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn('1');

        // BST: 20:00 London is 19:00 UTC
        $london = new \DateTimeZone('Europe/London');
        $this->assertTrue(
            $this->sut->hasSmsForRecipientBetween(
                '+447700900123',
                'letter-issued-sms',
                new \DateTimeImmutable('2026-06-01 20:00:00', $london),
                new \DateTimeImmutable('2026-06-02 20:00:00', $london)
            )
        );

        $this->assertSame(
            'SELECT COUNT(m.id) FROM ' . Entity::class . ' m'
            . ' WHERE m.channel = :channel AND m.recipient = :recipient AND m.templateName = :templateName'
            . ' AND m.status <> :cancelled'
            . ' AND ((m.requestedOn >= :start AND m.requestedOn < :end) OR (m.sentOn >= :start AND m.sentOn < :end))',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::CHANNEL_SMS, $qb->getParameter('channel')->getValue());
        $this->assertSame('+447700900123', $qb->getParameter('recipient')->getValue());
        $this->assertSame('letter-issued-sms', $qb->getParameter('templateName')->getValue());
        $this->assertSame(Entity::STATUS_CANCELLED, $qb->getParameter('cancelled')->getValue());

        foreach (['start' => '2026-06-01 19:00:00', 'end' => '2026-06-02 19:00:00'] as $name => $expected) {
            $value = $qb->getParameter($name)->getValue();
            // VOL's datetime type only takes mutable DateTime
            $this->assertInstanceOf(\DateTime::class, $value);
            $this->assertSame('UTC', $value->getTimezone()->getName());
            $this->assertSame($expected, $value->format('Y-m-d H:i:s'));
        }
    }

    public function testHasSmsForRecipientBetweenFalseWhenNone(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn('0');

        $this->assertFalse(
            $this->sut->hasSmsForRecipientBetween('+447700900123', 'letter-issued-sms', new \DateTime('2026-11-02 20:00:00'), new \DateTime('2026-11-03 20:00:00'))
        );
    }

    public function testHasOtherSmsSentToRecipientBetweenLooksAtSendTimeAndSkipsThisRow(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn('1');

        $london = new \DateTimeZone('Europe/London');
        $this->assertTrue(
            $this->sut->hasOtherSmsSentToRecipientBetween(
                42,
                '+447700900123',
                'letter-issued-sms',
                new \DateTimeImmutable('2026-11-04 20:00:00', $london),
                new \DateTimeImmutable('2026-11-05 20:00:00', $london)
            )
        );

        $this->assertSame(
            'SELECT COUNT(m.id) FROM ' . Entity::class . ' m'
            . ' WHERE m.channel = :channel AND m.recipient = :recipient AND m.templateName = :templateName'
            . ' AND m.status <> :cancelled'
            . ' AND m.id <> :exceptId AND m.sentOn >= :start AND m.sentOn < :end',
            $qb->getDQL(),
        );
        $this->assertSame(42, $qb->getParameter('exceptId')->getValue());
        $this->assertSame('2026-11-04 20:00:00', $qb->getParameter('start')->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $qb->getParameter('end')->getValue()->getTimezone()->getName());
    }

    public function testHasOtherSmsSentToRecipientBetweenFalseWhenNone(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn('0');

        $this->assertFalse(
            $this->sut->hasOtherSmsSentToRecipientBetween(42, '+447700900123', 'letter-issued-sms', new \DateTime('2026-11-04 20:00:00'), new \DateTime('2026-11-05 20:00:00'))
        );
    }

    private function message(): Entity
    {
        $message = new Entity(
            new RefData(Entity::CHANNEL_SMS),
            new RefData(Entity::STATUS_QUEUED),
            '+447700900123',
            // BST, so 10:30 London is 09:30 UTC
            new \DateTimeImmutable('2026-07-01 10:30:00', new \DateTimeZone('Europe/London'))
        );
        $message->setTemplateName('letter-issued-sms');
        $message->setDescription('A letter');
        $message->setOrganisation(m::mock(Organisation::class)->makePartial()->setId(3));
        $message->setLicence(m::mock(Licence::class)->makePartial()->setId(8001));
        $message->setDocument(m::mock(Document::class)->makePartial()->setId(7001));

        return $message;
    }

    private function connection(): m\MockInterface
    {
        $connection = m::mock(Connection::class);
        $this->em->shouldReceive('getConnection')->andReturn($connection);

        return $connection;
    }

    private static function driverError(string $message): DriverException
    {
        return new class ($message) extends DriverException {
        };
    }

    public function testInsertQueuedSmsWritesBothRowsInsideASavepoint(): void
    {
        $connection = $this->connection();
        $connection->expects('beginTransaction')->ordered();
        $connection->expects('insert')->ordered()->andReturnUsing(
            function (string $table, array $row): int {
                $this->assertSame('notify_message', $table);
                $this->assertSame(Entity::CHANNEL_SMS, $row['channel']);
                $this->assertSame(Entity::STATUS_QUEUED, $row['status']);
                $this->assertSame('+447700900123', $row['recipient']);
                $this->assertSame('letter-issued-sms', $row['template_name']);
                $this->assertSame('A letter', $row['description']);
                $this->assertSame(3, $row['organisation_id']);
                $this->assertSame(8001, $row['licence_id']);
                $this->assertSame(7001, $row['document_id']);
                $this->assertNull($row['application_id']);
                $this->assertNull($row['bus_reg_id']);
                $this->assertNull($row['case_id']);
                $this->assertSame('2026-07-01 09:30:00', $row['requested_on']);
                $this->assertSame(0, $row['attempts']);
                $this->assertSame(42, $row['created_by']);
                $this->assertSame(42, $row['last_modified_by']);
                $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $row['created_on']);
                $this->assertSame(1, $row['version']);

                return 1;
            }
        );
        $connection->expects('lastInsertId')->ordered()->andReturn('555');
        $connection->expects('insert')->ordered()->andReturnUsing(
            function (string $table, array $row): int {
                $this->assertSame('queue', $table);
                $this->assertSame(Queue::TYPE_SMS, $row['type']);
                $this->assertSame(Queue::STATUS_QUEUED, $row['status']);
                $this->assertSame(555, $row['entity_id']);
                $this->assertSame(
                    ['commandClass' => SendSms::class, 'commandData' => ['id' => 555]],
                    json_decode($row['options'], true)
                );
                // 08:00 London in BST
                $this->assertSame('2026-07-02 07:00:00', $row['process_after_date']);
                $this->assertSame(0, $row['attempts']);
                $this->assertSame(42, $row['created_by']);
                $this->assertSame(42, $row['last_modified_by']);
                $this->assertSame(1, $row['version']);

                return 1;
            }
        );
        $connection->expects('commit')->ordered();
        $connection->shouldNotReceive('rollBack');

        $id = $this->sut->insertQueuedSms(
            $this->message(),
            SendSms::class,
            new \DateTimeImmutable('2026-07-02 08:00:00', new \DateTimeZone('Europe/London')),
            42
        );

        $this->assertSame(555, $id);
    }

    public function testInsertQueuedSmsSendsStraightAwayWithoutAProcessAfterDate(): void
    {
        $connection = $this->connection();
        $connection->shouldReceive('beginTransaction', 'commit');
        $connection->shouldReceive('lastInsertId')->andReturn('555');
        $connection->shouldReceive('insert')->with('notify_message', m::any())->andReturn(1);
        $connection->expects('insert')->with('queue', m::on(fn(array $row) => $row['process_after_date'] === null))->andReturn(1);

        $this->assertSame(555, $this->sut->insertQueuedSms($this->message(), SendSms::class, null, null));
    }

    /** The letter's writes sit outside the savepoint, so it can still commit. */
    public function testFailedInsertRollsBackToTheSavepointAndThrowsSmsNotQueued(): void
    {
        $failure = new \RuntimeException('Data too long');
        $connection = $this->connection();
        $connection->expects('beginTransaction');
        $connection->expects('insert')->with('notify_message', m::any())->andReturn(1);
        $connection->expects('lastInsertId')->andReturn('555');
        $connection->expects('insert')->with('queue', m::any())->andThrow($failure);
        $connection->expects('rollBack');
        $connection->shouldNotReceive('commit');

        try {
            $this->sut->insertQueuedSms($this->message(), SendSms::class, null, 42);
            $this->fail('expected SmsNotQueued');
        } catch (SmsNotQueued $e) {
            $this->assertSame($failure, $e->getPrevious());
        }
    }

    /** A deadlock rolls back the letter as well, so it must reach the letter's handler. */
    public function testDeadlockIsPassedOn(): void
    {
        $deadlock = new DeadlockException(self::driverError('Deadlock found'), null);
        $connection = $this->connection();
        $connection->expects('beginTransaction');
        $connection->expects('insert')->andThrow($deadlock);
        $connection->expects('rollBack');

        $this->expectExceptionObject($deadlock);

        $this->sut->insertQueuedSms($this->message(), SendSms::class, null, 42);
    }

    /** No savepoint to go back to means no transaction either, so the original error is passed on. */
    public function testFailedRollbackPassesOnTheOriginalError(): void
    {
        $failure = new \RuntimeException('server gone away');
        $connection = $this->connection();
        $connection->expects('beginTransaction');
        $connection->expects('insert')->andThrow($failure);
        $connection->expects('rollBack')->andThrow(new \RuntimeException('SAVEPOINT does not exist'));

        $this->expectExceptionObject($failure);

        $this->sut->insertQueuedSms($this->message(), SendSms::class, null, 42);
    }
}
