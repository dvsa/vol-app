<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;

final class LetterInstanceTest extends RepositoryTestCase
{
    /** The choices and issues are read with the letters, not one letter at a time afterwards */
    private const string LETTERS_WITH_CHOICES_AND_ISSUES = 'SELECT m, c, lc, i, v FROM ' . Entity::class . ' m'
        . ' LEFT JOIN m.letterInstanceChoices c LEFT JOIN c.letterChoice lc'
        . ' LEFT JOIN m.letterInstanceIssues i LEFT JOIN i.letterIssueVersion v';

    private const string SENT_NEWEST_FIRST = ' AND m.status = :sent ORDER BY m.sentOn DESC, m.id DESC';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * Starting a new letter offers the letters of that type already sent for the same
     * application, newest first. Drafts and letters prepared but never sent aren't offered:
     * what they say may not be what the operator was told. A letter raised on a case that is on
     * the application carries the application too, but it isn't the application's letter.
     */
    public function testFetchLastSentFindsSentLettersRaisedOnAnApplication(): void
    {
        $letters = [new Entity(), new Entity()];

        $qb = $this->createRealQb();
        $this->sut->expects('getPaginator')->with($qb->stubbedQuery())->andReturn(new \ArrayIterator($letters));

        $this->assertSame($letters, $this->sut->fetchLastSent(7, 'application', 8, 10));

        $this->assertSame(
            self::LETTERS_WITH_CHOICES_AND_ISSUES
            . ' WHERE m.letterType = :letterType AND m.application = :raisedOn AND m.case IS NULL'
            . self::SENT_NEWEST_FIRST,
            $qb->getDQL()
        );
        $this->assertSame(7, $qb->getParameter('letterType')->getValue());
        $this->assertSame(8, $qb->getParameter('raisedOn')->getValue());
        $this->assertSame(Entity::STATUS_SENT, $qb->getParameter('sent')->getValue());
        $this->assertSame(10, $qb->getMaxResults());
    }

    /**
     * Every letter raised on an application, a case or a bus registration carries the licence
     * as well. Starting a letter on the licence only offers the ones raised on the licence
     * itself, so the caseworker isn't picking between letters for different applications.
     */
    public function testFetchLastSentForALicenceLeavesOutLettersRaisedOnItsApplicationsAndCases(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('getPaginator')->andReturn(new \ArrayIterator([]));

        $this->assertSame([], $this->sut->fetchLastSent(7, 'licence', 212, 10));

        $this->assertSame(
            self::LETTERS_WITH_CHOICES_AND_ISSUES
            . ' WHERE m.letterType = :letterType AND m.licence = :raisedOn'
            . ' AND m.case IS NULL AND m.application IS NULL AND m.busReg IS NULL'
            . ' AND m.transportManager IS NULL AND m.irfoOrganisation IS NULL'
            . self::SENT_NEWEST_FIRST,
            $qb->getDQL()
        );
    }

    /**
     * The link is put straight into the query, so only the letter's own links are allowed
     */
    public function testFetchLastSentRefusesALinkTheLetterDoesNotHave(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->sut->fetchLastSent(7, 'status', 8, 10);
    }
}
