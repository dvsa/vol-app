<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\UpdateDocumentAnalysisAssessmentStatus;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as DocumentAnalysisEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Api\Service\Idp\AnalysisReviewOutcome;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus as Cmd;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The real outcome rule and overlay are used, so these tests pin the approval rule as agreed:
 * every flagged check must read as a pass (fails and skips both block approval).
 */
final class UpdateDocumentAnalysisAssessmentStatusTest extends AbstractCommandHandlerTestCase
{
    private const STORED = ['version' => 1, 'rows' => ['stored' => 'payload']];

    private User|m\MockInterface $user;
    private AnalysisResultNormaliser|m\MockInterface $normaliser;

    public function setUp(): void
    {
        $this->normaliser = m::mock(AnalysisResultNormaliser::class);
        $this->sut = new UpdateDocumentAnalysisAssessmentStatus(
            $this->normaliser,
            new AnalysisAnnotationOverlay(),
            new AnalysisReviewOutcome()
        );
        $this->mockRepo('DocumentAnalysis', DocumentAnalysisRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        $this->user = m::mock(User::class);
        $this->mockedSmServices[AuthorizationService::class]
            ->allows('getIdentity->getUser')
            ->andReturn($this->user);

        parent::setUp();
    }

    /** Only approval is guarded, so these never read the analysis. */
    public static function statusProvider(): \Iterator
    {
        yield 'rejected' => [AssessmentStatus::REJECTED];
        yield 'pending' => [AssessmentStatus::PENDING];
    }

    #[DataProvider('statusProvider')]
    public function testHandleCommandRecordsTheReviewAsTheCurrentUser(AssessmentStatus $status): void
    {
        $command = Cmd::create(['id' => 5, 'status' => $status->value]);

        $this->repoMap['DocumentAnalysis']
            ->expects('recordAssessmentStatus')
            ->with(5, $status, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(5, $result->getId('documentAnalysis'));
        $this->assertSame(
            [sprintf('Document analysis 5 assessment status set to %s', $status->value)],
            $result->getMessages()
        );
    }

    /** No successful analysis with that id (missing, or not SUCCESS): nothing was written. */
    public function testHandleCommandThrowsWhenNoSuccessfulAnalysisMatches(): void
    {
        $this->repoMap['DocumentAnalysis']
            ->expects('recordAssessmentStatus')
            ->andReturn(0);

        $this->expectException(NotFoundException::class);

        // Rejected, so the approval guard (which reads the analysis first) is not involved.
        $this->sut->handleCommand(Cmd::create(['id' => 5, 'status' => 'REJECTED']));
    }

    public function testHandleCommandRefusesAnUnknownStatus(): void
    {
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAssessmentStatus');

        $this->expectException(\ValueError::class);

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'status' => 'SUCCESS']));
    }

    public function testApprovalIsRecordedWhenEveryCheckIsAPass(): void
    {
        $command = Cmd::create(['id' => 5, 'status' => AssessmentStatus::APPROVED->value]);
        $this->givenAnalysisWithFlags($command, [NormalisedResult::FLAG_PASS, NormalisedResult::FLAG_PASS]);

        $this->repoMap['DocumentAnalysis']
            ->expects('recordAssessmentStatus')
            ->with(5, AssessmentStatus::APPROVED, $this->user)
            ->andReturn(1);

        $this->assertSame(5, $this->sut->handleCommand($command)->getId('documentAnalysis'));
    }

    public static function blockingFlagProvider(): \Iterator
    {
        yield 'a fail' => [NormalisedResult::FLAG_FAIL];
        yield 'a skip' => [NormalisedResult::FLAG_SKIPPED];
    }

    /** The message tells the caseworker what to do; the internal app shows it as given. */
    #[DataProvider('blockingFlagProvider')]
    public function testApprovalIsRefusedWhileAnyCheckIsNotAPass(string $blockingFlag): void
    {
        $command = Cmd::create(['id' => 5, 'status' => AssessmentStatus::APPROVED->value]);
        $this->givenAnalysisWithFlags($command, [NormalisedResult::FLAG_PASS, $blockingFlag]);

        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAssessmentStatus');

        try {
            $this->sut->handleCommand($command);
            $this->fail('Approval should have been refused');
        } catch (ValidationException $e) {
            $this->assertSame(
                [
                    UpdateDocumentAnalysisAssessmentStatus::ERR_UNCHANGED_ISSUES =>
                        'Change all failed and skipped checks to a pass before you accept the financial evidence',
                ],
                $e->getMessages()
            );
        }
    }

    /**
     * @param list<string> $flags
     */
    private function givenAnalysisWithFlags(Cmd $command, array $flags): void
    {
        $rows = [];

        foreach ($flags as $index => $flag) {
            $rows['row' . $index] = ['flag' => $flag, 'remark' => null, 'value' => null, 'checks' => []];
        }

        $analysis = m::mock(DocumentAnalysisEntity::class);
        $analysis->allows('getResultNormalised')->andReturn(self::STORED);
        $analysis->allows('getAnnotations')->andReturn(null);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->with($command)->andReturn($analysis);
        $this->normaliser->allows('fromStored')->with(self::STORED)->andReturn(NormalisedResult::fromRows($rows));
    }
}
