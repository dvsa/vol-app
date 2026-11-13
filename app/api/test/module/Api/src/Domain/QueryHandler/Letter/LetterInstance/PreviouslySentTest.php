<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance\PreviouslySent as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\PreviouslySent as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

final class PreviouslySentTest extends QueryHandlerTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);

        parent::setUp();
    }

    private function issue(int $letterIssueId): LetterInstanceIssue
    {
        $letterIssue = new LetterIssue();
        $letterIssue->setId($letterIssueId);
        $version = new LetterIssueVersion();
        $version->setLetterIssue($letterIssue);
        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setLetterIssueVersion($version);

        return $instanceIssue;
    }

    private function choice(string $label, int $displayOrder): LetterInstanceChoice
    {
        $letterChoice = new LetterChoice();
        $letterChoice->setLabel($label);
        $letterChoice->setDisplayOrder($displayOrder);
        $instanceChoice = new LetterInstanceChoice();
        $instanceChoice->setLetterChoice($letterChoice);

        return $instanceChoice;
    }

    /**
     * Each letter comes back with when it was sent, the choices made for it (which is what tells
     * a first request from a final one) and the issues it covered, as the ids the New letter
     * form ticks.
     */
    public function testListsEachLetterWithItsChoicesAndIssues(): void
    {
        $letter = new LetterInstance();
        $letter->setId(392);
        // as it comes back from the database: the date columns are read as text
        $letter->setSentOn('2026-11-11 16:24:18');
        $letter->addLetterInstanceChoice($this->choice('Interim', 2));
        $letter->addLetterInstanceChoice($this->choice('First request', 1));
        // each issue is listed once whatever the letter holds, so the form can count what it ticks
        foreach ([15, 1, 15] as $letterIssueId) {
            $letter->addLetterInstanceIssue($this->issue($letterIssueId));
        }

        $query = Qry::create(['letterType' => 7, 'application' => 8]);
        $this->repoMap['LetterInstance']->expects('fetchLastSent')->with(7, 'application', 8, 10)->andReturn([$letter]);

        $this->assertSame(
            [
                'result' => [
                    [
                        'id' => 392,
                        'sentOn' => '2026-11-11T16:24:18+0000',
                        'choices' => ['First request', 'Interim'],
                        'issueIds' => [15, 1],
                    ],
                ],
                'count' => 1,
            ],
            $this->sut->handleQuery($query)
        );
    }

    /**
     * A letter raised on an application also carries its licence, as one raised on a case or a
     * bus registration does. Whatever the new letter is being started on is what's looked up.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('startedOn')]
    public function testLooksUpLettersForWhatTheNewLetterIsStartedOn(array $startedOn, string $relation, int $id): void
    {
        $this->repoMap['LetterInstance']->expects('fetchLastSent')->with(7, $relation, $id, 10)->andReturn([]);

        $this->assertSame(
            ['result' => [], 'count' => 0],
            $this->sut->handleQuery(Qry::create(['letterType' => 7] + $startedOn))
        );
    }

    public static function startedOn(): \Iterator
    {
        yield 'an application' => [['application' => 8], 'application', 8];
        yield 'a licence' => [['licence' => 212], 'licence', 212];
        yield 'a case' => [['case' => 24], 'case', 24];
        yield 'a bus registration' => [['busReg' => 3], 'busReg', 3];
        yield 'a transport manager' => [['transportManager' => 5], 'transportManager', 5];
        yield 'an IRFO operator' => [['irfoOrganisation' => 6], 'irfoOrganisation', 6];
        // the licence is the fallback the others all carry, so it's the last to be used
        yield 'an application, sent with its licence' => [['licence' => 212, 'application' => 8], 'application', 8];
        yield 'a case, sent with its application' => [['application' => 8, 'case' => 24], 'case', 24];
    }

    /**
     * Letters don't link to a permit application, so there's nothing to look them up by
     */
    public function testNothingIsOfferedWithoutSomethingToLookTheLettersUpBy(): void
    {
        $this->repoMap['LetterInstance']->shouldNotReceive('fetchLastSent');

        $this->assertSame(
            ['result' => [], 'count' => 0],
            $this->sut->handleQuery(Qry::create(['letterType' => 7, 'irhpApplication' => 9]))
        );
    }
}
