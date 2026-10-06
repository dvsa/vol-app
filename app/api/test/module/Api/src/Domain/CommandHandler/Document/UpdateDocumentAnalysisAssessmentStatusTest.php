<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\UpdateDocumentAnalysisAssessmentStatus;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus as Cmd;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

final class UpdateDocumentAnalysisAssessmentStatusTest extends AbstractCommandHandlerTestCase
{
    private User|m\MockInterface $user;

    public function setUp(): void
    {
        $this->sut = new UpdateDocumentAnalysisAssessmentStatus();
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

    public static function statusProvider(): \Iterator
    {
        yield 'approved' => [AssessmentStatus::APPROVED];
        yield 'rejected' => [AssessmentStatus::REJECTED];
        yield 'pending' => [AssessmentStatus::PENDING];
    }

    #[DataProvider('statusProvider')]
    public function testHandleCommandRecordsTheReviewAsTheCurrentUser(AssessmentStatus $status): void
    {
        $command = Cmd::create(['id' => 5, 'application' => 42, 'status' => $status->value]);

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

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'status' => 'APPROVED']));
    }

    public function testHandleCommandRefusesAnUnknownStatus(): void
    {
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAssessmentStatus');

        $this->expectException(\ValueError::class);

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'status' => 'SUCCESS']));
    }
}


