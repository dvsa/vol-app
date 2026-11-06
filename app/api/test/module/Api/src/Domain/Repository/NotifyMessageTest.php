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
            . ' AND m.requestedOn >= :start AND m.requestedOn < :end',
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
}
