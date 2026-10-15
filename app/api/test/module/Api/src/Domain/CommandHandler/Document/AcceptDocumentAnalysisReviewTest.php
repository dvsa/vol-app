<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\AcceptDocumentAnalysisReview;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Api\Service\Idp\AnalysisReviewOutcome;
use Dvsa\Olcs\Transfer\Command\Document\AcceptDocumentAnalysisReview as Cmd;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The handler decides the outcome from the stored result and records it; the command carries
 * only the analysis. AnalysisReviewOutcome has its own tests for the rule, so the real one is
 * used here with results built to pass or fail.
 */
final class AcceptDocumentAnalysisReviewTest extends AbstractCommandHandlerTestCase
{
    private const STORED = ['version' => 1, 'rows' => ['stored' => 'payload']];

    private User|m\MockInterface $user;
    private AnalysisResultNormaliser|m\MockInterface $normaliser;

    public function setUp(): void
    {
        $this->normaliser = m::mock(AnalysisResultNormaliser::class);
        $this->sut = new AcceptDocumentAnalysisReview($this->normaliser, new AnalysisReviewOutcome());
        $this->mockRepo('DocumentAnalysis', DocumentAnalysisRepo::class);

        $this->user = m::mock(User::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];
        $this->mockedSmServices[AuthorizationService::class]
            ->allows('getIdentity->getUser')
            ->andReturn($this->user);

        parent::setUp();
    }

    public static function outcomeProvider(): \Iterator
    {
        yield 'every flagged row passes' => ['pass', AssessmentStatus::APPROVED];
        yield 'a flagged row fails' => ['fail', AssessmentStatus::REJECTED];
    }

    #[DataProvider('outcomeProvider')]
    public function testDecidesTheOutcomeFromTheStoredResultAndRecordsItAsTheCurrentUser(
        string $flag,
        AssessmentStatus $expected
    ): void {
        $command = Cmd::create(['id' => 5]);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->with($command)
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->with(self::STORED)->andReturn($this->normalisedResult($flag));
        $this->repoMap['DocumentAnalysis']->expects('recordAssessmentStatus')
            ->with(5, $expected, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(5, $result->getId('documentAnalysis'));
        $this->assertSame($expected->value, $result->getFlag(AcceptDocumentAnalysisReview::FLAG_ASSESSMENT_STATUS));
        $this->assertSame(
            [sprintf('Document analysis 5 review accepted: %s', $expected->value)],
            $result->getMessages()
        );
    }

    /**
     * Only a successful analysis has anything to review. The analysis exists, so this is a bad
     * request (400) rather than not found: the action does not apply to it in its current state.
     */
    #[DataProvider('notSuccessfulProvider')]
    public function testRefusesAnAnalysisThatIsNotSuccessful(string $status): void
    {
        $command = Cmd::create(['id' => 5]);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis($status));
        $this->normaliser->shouldNotReceive('fromStored');
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAssessmentStatus');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(sprintf('Document analysis 5 is %s, not SUCCESS', $status));

        $this->sut->handleCommand($command);
    }

    public static function notSuccessfulProvider(): \Iterator
    {
        yield 'pending' => [Entity::STATUS_PENDING];
        yield 'error' => [Entity::STATUS_ERROR];
        yield 'timeout' => [Entity::STATUS_TIMEOUT];
    }

    /** A stored report the normaliser could not read, or one with no flagged rows: nothing to decide on. */
    #[DataProvider('nothingToDecideProvider')]
    public function testRefusesAnAnalysisWithNoAssessmentToDecideOn(?NormalisedResult $normalised): void
    {
        $command = Cmd::create(['id' => 5]);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($normalised);
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAssessmentStatus');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Document analysis 5 has no assessment to review');

        $this->sut->handleCommand($command);
    }

    public static function nothingToDecideProvider(): \Iterator
    {
        yield 'no normalised result' => [null];
        yield 'no flagged rows' => [NormalisedResult::fromRows([
            'bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []],
        ])];
    }

    /** The row stopped being SUCCESS between the read and the guarded write. */
    public function testThrowsWhenTheGuardedWriteMatchesNothing(): void
    {
        $command = Cmd::create(['id' => 5]);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->expects('recordAssessmentStatus')->andReturn(0);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Document analysis 5 is no longer a successful analysis');

        $this->sut->handleCommand($command);
    }

    private function analysis(string $status): Entity|m\MockInterface
    {
        $analysis = m::mock(Entity::class);
        $analysis->allows('getStatus')->andReturn($status);
        $analysis->allows('getResultNormalised')->andReturn(self::STORED);

        return $analysis;
    }

    /** A current result whose six flagged rows all carry $flag. */
    private function normalisedResult(string $flag): NormalisedResult
    {
        $rows = ['bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []]];

        foreach (['authenticity', 'name', 'statementDate', 'statementPeriod', 'averageFunds', 'largeDeposit'] as $key) {
            $rows[$key] = ['flag' => $flag, 'remark' => null, 'value' => null, 'checks' => []];
        }

        return NormalisedResult::fromRows($rows);
    }
}
