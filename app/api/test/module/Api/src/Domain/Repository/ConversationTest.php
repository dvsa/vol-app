<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Conversation as Repo;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation as Entity;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead;
use Dvsa\OlcsTest\Support\TestQueryBuilder;

final class ConversationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * A conversation is unread when it is still open and none of the reader's roles has recorded a
     * read against its latest message. That is expressed as a correlated EXISTS, once in the
     * has_unread projection and again in the WHERE, so the two must agree: they are built from
     * separate query builders and could drift apart.
     */
    public function testApplyOrderForListing(): void
    {
        $qb = $this->createRealQb();

        // The two EXISTS sub-selects are built off the EntityManager, one each.
        $this->em->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturnValues([$this->newRealQb(), $this->newRealQb()]);

        $this->assertSame($qb, $this->sut->applyOrderForListing($qb, ['role1', 'role2']));

        $unreadSubQuery = 'SELECT 1 FROM ' . MessagingUserMessageRead::class . ' %1$s_read'
            . ' INNER JOIN %1$s_read.user %1$s_user INNER JOIN %1$s_user.roles %1$s_role'
            . ' WHERE %1$s_read.messagingMessage = irmm.id'
            . ' AND %1$s_role.role IN(:roleNames)';

        $this->assertSame(
            'SELECT m, MAX(irmm.createdOn) AS last_read,'
            // The projection interpolates the sub-query's DQL itself, so it renders EXISTS (...).
            . ' CASE WHEN m.isClosed = 0 AND NOT EXISTS (' . sprintf($unreadSubQuery, 'inner') . ')'
            . ' THEN 1 ELSE 0 END AS has_unread'
            . self::FROM
            . ' LEFT JOIN ' . MessagingMessage::class . ' irmm'
            . ' WITH irmm.messagingConversation = m.id'
            . ' LEFT JOIN irmm.messagingConversation inner_conversation'
            . ' LEFT JOIN inner_conversation.task inner_task'
            // The filter goes through expr()->exists(), which renders EXISTS(...) and brackets it.
            . ' WHERE m.isClosed = :closed'
            . ' OR (EXISTS(' . sprintf($unreadSubQuery, 'filter') . '))'
            . ' GROUP BY m.id'
            . ' ORDER BY m.isClosed ASC, has_unread DESC, last_read DESC',
            $qb->getDQL(),
        );
        $this->assertSame(['role1', 'role2'], $qb->getParameter('roleNames')->getValue());
        $this->assertFalse($qb->getParameter('closed')->getValue());
    }

    /**
     * Asking for both statuses is the same as asking for neither, so the filter drops out rather
     * than producing a contradiction.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('statusesProvider')]
    public function testFilterByStatuses(array $statuses, string $expectedWhere): void
    {
        $qb = $this->createRealQb();

        $this->assertSame($qb, $this->sut->filterByStatuses($qb, $statuses));

        $this->assertSame('SELECT m' . self::FROM . $expectedWhere, $qb->getDQL());
    }

    public static function statusesProvider(): \Iterator
    {
        yield 'open' => [['open'], ' WHERE m.isClosed = 0'];
        yield 'closed' => [['closed'], ' WHERE m.isClosed = 1'];
        yield 'both' => [['open', 'closed'], ' WHERE m.isClosed = 0 OR m.isClosed = 1'];
        yield 'neither' => [[], ''];
        yield 'an unknown status' => [['archived'], ''];
    }

    public function testFilterByLicenceId(): void
    {
        $qb = $this->createRealQb();

        $this->assertSame($qb, $this->sut->filterByLicenceId($qb, 7));

        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.task t'
            . ' WHERE t.licence IS NOT NULL AND t.licence = :licence',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }
}
