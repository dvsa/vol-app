<?php

declare(strict_types=1);

namespace OlcsTest\Service\FinancialEvidence;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\FinancialEvidence\FinancialEvidenceAssessmentService;
use Olcs\Service\FinancialEvidence\FinancialEvidenceAssessmentServiceFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;

final class FinancialEvidenceAssessmentServiceTest extends MockeryTestCase
{
    /** Approved only when every flag is PASS; a FAIL or a SKIPPED (a check not made) rejects. */
    public static function flagsProvider(): \Iterator
    {
        yield 'all pass' => [['PASS', 'PASS', 'PASS', 'PASS', 'PASS', 'PASS'], AssessmentStatus::APPROVED];
        yield 'one skipped' => [['PASS', 'SKIPPED', 'PASS', 'PASS', 'PASS', 'PASS'], AssessmentStatus::REJECTED];
        yield 'one fail' => [['PASS', 'PASS', 'FAIL', 'PASS', 'PASS', 'PASS'], AssessmentStatus::REJECTED];
        yield 'all fail' => [['FAIL', 'FAIL', 'FAIL', 'FAIL', 'FAIL', 'FAIL'], AssessmentStatus::REJECTED];
    }

    #[DataProvider('flagsProvider')]
    public function testDecide(array $flags, AssessmentStatus $expected): void
    {
        $sut = new FinancialEvidenceAssessmentService(m::mock(CommandSender::class));

        $this->assertSame($expected, $sut->decide($flags));
    }

    public function testDecideRefusesNoFlags(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new FinancialEvidenceAssessmentService(m::mock(CommandSender::class)))->decide([]);
    }

    #[DataProvider('flagsProvider')]
    public function testAssessRecordsTheOutcomeScopedToTheContext(array $flags, AssessmentStatus $expected): void
    {
        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturnTrue();

        $commandSender = m::mock(CommandSender::class);
        $commandSender->expects('send')
            ->with(m::on(static fn($command): bool => $command instanceof UpdateDocumentAnalysisAssessmentStatus
                && (int)$command->getId() === 9
                && $command->getStatus() === $expected->value
                && (int)$command->getApplication() === 42
                && $command->getLicence() === null))
            ->andReturn($response);

        $sut = new FinancialEvidenceAssessmentService($commandSender);

        $this->assertSame($expected, $sut->assess(9, $flags, 'application', 42));
    }

    public function testAssessScopesToALicence(): void
    {
        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturnTrue();

        $commandSender = m::mock(CommandSender::class);
        $commandSender->expects('send')
            ->with(m::on(static fn($command): bool => (int)$command->getLicence() === 7
                && $command->getApplication() === null))
            ->andReturn($response);

        $sut = new FinancialEvidenceAssessmentService($commandSender);

        $this->assertSame(AssessmentStatus::APPROVED, $sut->assess(9, ['PASS'], 'licence', 7));
    }

    public function testAssessReturnsNullWhenTheApiDoesNotRecordIt(): void
    {
        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturnFalse();

        $commandSender = m::mock(CommandSender::class);
        $commandSender->expects('send')->andReturn($response);

        $sut = new FinancialEvidenceAssessmentService($commandSender);

        $this->assertNull($sut->assess(9, ['PASS'], 'licence', 7));
    }

    public function testFactoryUsesTheSharedCommandSender(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('CommandSender')->andReturn(m::mock(CommandSender::class));

        $this->assertInstanceOf(
            FinancialEvidenceAssessmentService::class,
            (new FinancialEvidenceAssessmentServiceFactory())($container, FinancialEvidenceAssessmentService::class)
        );
    }
}

