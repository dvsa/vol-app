<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\NotifyMessage as Repo;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;

final class NotifyMessageTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testHasSmsForRecipientOnBuildsDayRangeQuery(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn('1');

        $this->assertTrue(
            $this->sut->hasSmsForRecipientOn('+447700900123', new \DateTimeImmutable('2026-11-05 12:00:00', new \DateTimeZone('Europe/London')))
        );

        $this->assertSame(
            'SELECT COUNT(m.id) FROM ' . Entity::class . ' m'
            . ' WHERE m.channel = :channel AND m.recipient = :recipient AND m.status <> :cancelled'
            . ' AND m.requestedOn >= :dayStart AND m.requestedOn < :dayEnd',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::CHANNEL_SMS, $qb->getParameter('channel')->getValue());
        $this->assertSame('+447700900123', $qb->getParameter('recipient')->getValue());
        $this->assertSame(Entity::STATUS_CANCELLED, $qb->getParameter('cancelled')->getValue());
        // November: London is on GMT, so UTC bounds equal the London midnights.
        $this->assertSame('2026-11-05 00:00:00', $qb->getParameter('dayStart')->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-06 00:00:00', $qb->getParameter('dayEnd')->getValue()->format('Y-m-d H:i:s'));
    }

    /** requested_on is stored in UTC, so in summer the London day starts at 23:00 UTC the day before. */
    public function testHasSmsForRecipientOnConvertsBritishSummerTimeToUtc(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn('0');

        $this->assertFalse(
            $this->sut->hasSmsForRecipientOn('+447700900123', new \DateTimeImmutable('2026-07-01 00:30:00', new \DateTimeZone('Europe/London')))
        );

        $this->assertSame('2026-06-30 23:00:00', $qb->getParameter('dayStart')->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-01 23:00:00', $qb->getParameter('dayEnd')->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $qb->getParameter('dayStart')->getValue()->getTimezone()->getName());
    }
}
