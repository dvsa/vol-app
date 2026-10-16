<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox as Entity;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox::class)]
final class CorrespondenceInboxTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\CorrespondenceInbox::class);
    }

    /**
     * Welsh correspondence is excluded from printing; the emailReminderSent flag deliberately
     * is not consulted, since a sent reminder does not remove the need to print.
     */
    public function testGetAllRequiringPrint(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()
            ->expects('setFetchMode')
            ->with(Entity::class, 'document', ClassMetadata::FETCH_EAGER)
            ->andReturnSelf();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->getAllRequiringPrint('2015-01-01', '2016-01-01'));

        $this->assertSame(
            'SELECT m, d, l' . self::FROM
            . ' INNER JOIN m.document d INNER JOIN m.licence l'
            . ' WHERE l.translateToWelsh = 0 AND m.accessed = 0'
            . ' AND m.createdOn >= :minDate AND m.createdOn <= :maxDate'
            . ' AND m.printed = 0 AND l.id IS NOT NULL',
            $qb->getDQL(),
        );
        $this->assertSame('2015-01-01', $qb->getParameter('minDate')->getValue());
        $this->assertSame('2016-01-01', $qb->getParameter('maxDate')->getValue());
    }

    /**
     * Reminders additionally skip anything already printed, so an organisation with no email
     * address is not chased indefinitely past the print threshold.
     */
    public function testGetAllRequiringReminder(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()
            ->expects('setFetchMode')
            ->with(Entity::class, 'document', ClassMetadata::FETCH_EAGER)
            ->andReturnSelf();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->getAllRequiringReminder('2015-01-01', '2016-01-01'));

        $this->assertSame(
            'SELECT m, d, l, lo, lou, louu, louucd, cd, cdd' . self::FROM
            . ' INNER JOIN m.document d INNER JOIN m.licence l INNER JOIN l.organisation lo'
            . ' INNER JOIN lo.organisationUsers lou INNER JOIN lou.user louu'
            . ' INNER JOIN louu.contactDetails louucd'
            . ' LEFT JOIN d.continuationDetails cd LEFT JOIN cd.checklistDocument cdd'
            . ' WHERE m.accessed = 0 AND m.createdOn >= :minDate AND m.createdOn <= :maxDate'
            . ' AND m.emailReminderSent = 0 AND m.printed = 0 AND l.id IS NOT NULL'
            . ' AND l.translateToWelsh = 0',
            $qb->getDQL(),
        );
    }

    public function testFetchByDocumentId(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByDocumentId(7));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.document = :document',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('document')->getValue());
    }
}
