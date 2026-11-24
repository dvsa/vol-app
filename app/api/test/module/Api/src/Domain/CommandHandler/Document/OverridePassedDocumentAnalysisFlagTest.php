<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\OverridePassedDocumentAnalysisFlag;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

final class OverridePassedDocumentAnalysisFlagTest extends AbstractCommandHandlerTestCase
{
    private const STORED = ['version' => 1, 'rows' => ['stored' => 'payload']];

    private User|m\MockInterface $user;
    private AnalysisResultNormaliser|m\MockInterface $normaliser;

    public function setUp(): void
    {
        $this->normaliser = m::mock(AnalysisResultNormaliser::class);
        $this->sut = new OverridePassedDocumentAnalysisFlag($this->normaliser, new AnalysisAnnotationOverlay());
        $this->mockRepo('DocumentAnalysis', DocumentAnalysisRepo::class);

        $this->user = m::mock(User::class);
        $this->user->allows('getId')->andReturn(99);
        $this->user->allows('getContactDetails')->andReturnNull();
        $this->user->allows('getLoginId')->andReturn('jbloggs');

        $this->mockedSmServices = [AuthorizationService::class => m::mock(AuthorizationService::class)];
        $this->mockedSmServices[AuthorizationService::class]
            ->allows('getIdentity->getUser')
            ->andReturn($this->user);

        parent::setUp();
    }

    public function testRecordsTheChangeAsAnAnnotationAndLeavesTheResultAlone(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'largeDeposit', 'comment' => 'Checked by hand', 'changeRecommendation' => 'Y']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->with($command)
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->with(self::STORED)->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static function (array $annotations): bool {
                $row = $annotations['rows']['largeDeposit'] ?? null;

                return $row !== null
                    && $row['flag'] === 'fail'
                    && $row['originalFlag'] === 'pass'
                    && $row['comment'] === 'Checked by hand'
                    && $row['changedBy'] === ['id' => 99, 'name' => 'jbloggs'];
            }), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(5, $result->getId('documentAnalysis'));
        $this->assertSame(['Document analysis 5 check largeDeposit changed to fail'], $result->getMessages());
    }

    /**
     * changeRecommendation "N" with a comment still given is saved as a note: the annotation's
     * flag is the row's own current flag, so the row is not changed to a fail.
     */
    public function testANoteWithoutChangingTheFlagRecordsTheCommentOnly(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'largeDeposit', 'comment' => 'Just a note', 'changeRecommendation' => 'N']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->with($command)
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->with(self::STORED)->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static function (array $annotations): bool {
                $row = $annotations['rows']['largeDeposit'] ?? null;

                return $row !== null
                    && $row['flag'] === 'pass'
                    && $row['originalFlag'] === 'pass'
                    && $row['comment'] === 'Just a note';
            }), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Document analysis 5 check largeDeposit comment saved'], $result->getMessages());
    }

    public function testKeepsEarlierAnnotationsOnOtherRows(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'largeDeposit', 'comment' => 'Because', 'changeRecommendation' => 'Y']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierAnnotationOnName(), 4));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(
                5,
                m::on(static fn(array $a): bool => isset($a['rows']['name'], $a['rows']['largeDeposit'])),
                4,
                $this->user
            )
            ->andReturn(1);

        $this->sut->handleCommand($command);
    }

    public static function notSuccessfulProvider(): \Iterator
    {
        yield 'pending' => [Entity::STATUS_PENDING];
        yield 'error' => [Entity::STATUS_ERROR];
        yield 'timeout' => [Entity::STATUS_TIMEOUT];
    }

    #[DataProvider('notSuccessfulProvider')]
    public function testRefusesAnAnalysisThatIsNotSuccessful(string $status): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis($status));
        $this->normaliser->shouldNotReceive('fromStored');
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(sprintf('Document analysis 5 is %s, not SUCCESS', $status));

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    public function testRefusesAnAnalysisWithNoAssessment(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturnNull();
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Document analysis 5 has no assessment to change');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    public static function notPassProvider(): \Iterator
    {
        yield 'fail' => ['fail'];
        yield 'skipped' => ['skipped'];
    }

    #[DataProvider('notPassProvider')]
    public function testRefusesACheckTheAnalyserDidNotPass(string $flag): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult($flag));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Check name of document analysis 5 was not assessed as a pass');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    /**
     * The caseworker's answer on a passed check can be revisited (agreed with UX): the form stays
     * on a changed check so a typo can be fixed or the comment edited. "Y" again keeps it a fail
     * and replaces the comment.
     */
    public function testYesOnACheckAlreadyChangedToAFailUpdatesTheComment(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'A better reason', 'changeRecommendation' => 'Y']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierFailAnnotationOnName(), 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => $a['rows']['name']['flag'] === 'fail'
                && $a['rows']['name']['originalFlag'] === 'pass'
                && $a['rows']['name']['comment'] === 'A better reason'), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Document analysis 5 check name changed to fail'], $result->getMessages());
    }

    /** "N" on a check that was changed to a fail changes it back to the pass the analyser gave it. */
    public function testNoOnACheckChangedToAFailChangesItBackToAPass(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'Changed in error', 'changeRecommendation' => 'N']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierFailAnnotationOnName(), 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => $a['rows']['name']['flag'] === 'pass'
                && $a['rows']['name']['originalFlag'] === 'pass'
                && $a['rows']['name']['comment'] === 'Changed in error'), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Document analysis 5 check name changed back to pass'], $result->getMessages());
    }

    /**
     * "N" means the analyser's outcome stands. With nothing to say, the caseworker's entry for
     * the check is deleted, so the check reads exactly as the analyser left it: a change to a
     * fail is undone and a note is removed alike. Earlier entries are still in
     * document_analysis_hist. Other checks' entries are kept.
     */
    #[DataProvider('existingEntryProvider')]
    public function testNoWithNoCommentDeletesTheCaseworkersEntryForTheCheck(string $entryFlag, string $message): void
    {
        $annotations = $this->earlierFailAnnotationOnName();
        $annotations['rows']['name']['flag'] = $entryFlag;
        $annotations['rows']['largeDeposit'] = $annotations['rows']['name'];

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $annotations, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => array_keys($a['rows']) === ['largeDeposit']), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => '', 'changeRecommendation' => 'N'])
        );

        $this->assertSame([$message], $result->getMessages());
    }

    public static function existingEntryProvider(): \Iterator
    {
        yield 'a change to a fail' => ['fail', 'Document analysis 5 check name changed back to pass'];
        yield 'a note' => ['pass', 'Document analysis 5 check name comment removed'];
    }

    /** Nothing to delete and nothing to say: nothing is written, so the version is not bumped. */
    public function testNoWithNoCommentOnACheckNobodyHasAnsweredWritesNothing(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => '', 'changeRecommendation' => 'N'])
        );

        $this->assertSame(['Document analysis 5 check name left as it is'], $result->getMessages());
    }

    /**
     * Changing a passed check to a fail needs a reason, and that is enforced here, not only by
     * the internal app's form: any caller of the API is refused, so no pass can be overturned
     * with nothing on record to say why. It holds for a check already failed too, where "Y" with
     * no text would otherwise blank the reason on record.
     */
    #[DataProvider('noReasonProvider')]
    public function testYesWithoutAReasonIsRefused(string $comment, ?string $existingEntryFlag): void
    {
        $annotations = $existingEntryFlag === null ? null : $this->earlierFailAnnotationOnName();

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $annotations, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        try {
            $this->sut->handleCommand(
                Cmd::create(['id' => 5, 'row' => 'name', 'comment' => $comment, 'changeRecommendation' => 'Y'])
            );
            $this->fail('Expected the change to be refused for want of a reason');
        } catch (ValidationException $e) {
            $this->assertSame(
                [
                    OverridePassedDocumentAnalysisFlag::ERR_REASON_REQUIRED =>
                        OverridePassedDocumentAnalysisFlag::MSG_REASON_REQUIRED,
                ],
                $e->getMessages()
            );
        }
    }

    public static function noReasonProvider(): \Iterator
    {
        yield 'empty' => ['', null];
        yield 'only spaces' => ['   ', null];
        yield 'empty, on a check already failed' => ['', 'fail'];
    }

    /**
     * Which checks this command can answer is asked of the analyser's own flag. A check the
     * analyser failed is not one of them, even after a caseworker has changed it to a pass: that
     * one belongs to OverrideDocumentAnalysisFlag.
     */
    public function testRefusesACheckTheAnalyserFailedEvenOnceChangedToAPass(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierAnnotationOnName()));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('was not assessed as a pass');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x', 'changeRecommendation' => 'Y']));
    }

    public function testRefusesAnUnknownRow(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'nonsense', 'comment' => 'x']));
    }

    /** Someone else changed the row between the read and the version-guarded write. */
    public function testThrowsWhenTheVersionGuardedWriteMatchesNothing(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')->andReturn(0);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('changed while it was being reviewed');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    private function earlierAnnotationOnName(): array
    {
        return ['rows' => ['name' => [
            'flag' => 'pass',
            'originalFlag' => 'fail',
            'comment' => 'x',
            'changedBy' => ['id' => 1, 'name' => 'A'],
            'changedOn' => '2026-10-01 10:00:00',
        ]]];
    }

    private function earlierFailAnnotationOnName(): array
    {
        return ['rows' => ['name' => [
            'flag' => 'fail',
            'originalFlag' => 'pass',
            'comment' => 'x',
            'changedBy' => ['id' => 1, 'name' => 'A'],
            'changedOn' => '2026-10-01 10:00:00',
        ]]];
    }

    private function analysis(string $status, ?array $annotations = null, int $version = 1): Entity|m\MockInterface
    {
        $analysis = m::mock(Entity::class);
        $analysis->allows('getStatus')->andReturn($status);
        $analysis->allows('getResultNormalised')->andReturn(self::STORED);
        $analysis->allows('getAnnotations')->andReturn($annotations);
        $analysis->allows('getVersion')->andReturn($version);

        return $analysis;
    }

    private function normalisedResult(string $flag): NormalisedResult
    {
        $rows = ['bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []]];

        foreach (['authenticity', 'name', 'statementDate', 'statementPeriod', 'averageFunds', 'largeDeposit'] as $key) {
            $rows[$key] = ['flag' => $flag, 'remark' => null, 'value' => null, 'checks' => []];
        }

        return NormalisedResult::fromRows($rows);
    }
}
