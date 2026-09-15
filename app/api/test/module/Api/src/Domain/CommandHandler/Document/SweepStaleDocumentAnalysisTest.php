<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\SweepStaleDocumentAnalysis as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\SweepStaleDocumentAnalysis;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as DocAnalysisEntity;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Mockery as m;

final class SweepStaleDocumentAnalysisTest extends TestCase
{
    private m\MockInterface $analysisRepo;
    private SweepStaleDocumentAnalysis $sut;

    protected function tearDown(): void
    {
        m::close();
    }

    public function setUp(): void
    {
        $this->analysisRepo = m::mock(DocumentAnalysisRepo::class);

        $repoManager = m::mock(RepositoryServiceManager::class);
        $repoManager->shouldReceive('get')->with(DocumentAnalysisRepo::class)->andReturn($this->analysisRepo);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn([
            'idp' => ['sweeper_threshold_minutes' => 90],
        ]);
        $container->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($repoManager);
        $container->shouldReceive('get')->with('TransactionManager')->andReturn(m::mock(TransactionManagerInterface::class));
        $container->shouldReceive('get')->with('CommandHandlerManager')->andReturn(m::mock(CommandHandlerManager::class));
        $container->shouldReceive('get')->with('QueryHandlerManager')->andReturn(m::mock(QueryHandlerManager::class));
        $container->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn(m::mock(IdentityProviderInterface::class));

        $sut = new SweepStaleDocumentAnalysis();
        $this->sut = $sut->__invoke($container, null);
    }

    public function testSweepsUsingTheConfiguredThresholdWhenTheCommandDoesNotOverrideIt(): void
    {
        $this->analysisRepo->shouldReceive('fetchStalePending')->once()->andReturn([]);

        $captured = null;
        $this->analysisRepo->shouldReceive('sweepStalePending')
            ->once()
            ->andReturnUsing(function (\DateTimeInterface $threshold) use (&$captured): int {
                $captured = $threshold;
                return 3;
            });

        $result = $this->sut->handleCommand(Cmd::create([]));

        $ageMinutes = (new \DateTimeImmutable())->getTimestamp() - $captured->getTimestamp();
        $this->assertEqualsWithDelta(90 * 60, $ageMinutes, 5, 'uses idp.sweeper_threshold_minutes');
        $this->assertContains(
            'Swept 3 stale document analysis row(s) to TIMEOUT (threshold 90 minutes)',
            $result->getMessages()
        );
    }

    public function testLogsStaleRowsWithoutErrorWhenCreatedOnIsHydratedAsAString(): void
    {
        // Regression test: getCreatedOn() (without $asDateTime=true) can return the raw
        // stored value rather than a \DateTime, which previously caused errors
        $staleAnalysis = m::mock(DocAnalysisEntity::class);
        $staleAnalysis->shouldReceive('getTokenString')->andReturn('abc123');
        $staleAnalysis->shouldReceive('getApplication')->andReturn(null);
        $staleAnalysis->shouldReceive('getDocument')->andReturn(null);
        $staleAnalysis->shouldReceive('getCreatedOn')->with(true)->andReturn(new \DateTimeImmutable('-2 hours'));

        $this->analysisRepo->shouldReceive('fetchStalePending')->once()->andReturn([$staleAnalysis]);
        $this->analysisRepo->shouldReceive('sweepStalePending')->once()->andReturn(1);

        $result = $this->sut->handleCommand(Cmd::create([]));

        $this->assertContains(
            'Swept 1 stale document analysis row(s) to TIMEOUT (threshold 90 minutes)',
            $result->getMessages()
        );
    }

    public function testCommandThresholdOverridesTheConfiguredDefault(): void
    {
        $this->analysisRepo->shouldReceive('fetchStalePending')->once()->andReturn([]);

        $captured = null;
        $this->analysisRepo->shouldReceive('sweepStalePending')
            ->once()
            ->andReturnUsing(function (\DateTimeInterface $threshold) use (&$captured): int {
                $captured = $threshold;
                return 0;
            });

        $this->sut->handleCommand(Cmd::create(['thresholdMinutes' => 15]));

        $ageMinutes = (new \DateTimeImmutable())->getTimestamp() - $captured->getTimestamp();
        $this->assertEqualsWithDelta(15 * 60, $ageMinutes, 5);
    }

    public function testFallsBackToTheDefaultWhenConfiguredThresholdIsNonPositive(): void
    {
        $repoManager = m::mock(RepositoryServiceManager::class);
        $repoManager->shouldReceive('get')->with(DocumentAnalysisRepo::class)->andReturn($this->analysisRepo);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn(['idp' => ['sweeper_threshold_minutes' => 0]]);
        $container->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($repoManager);
        $container->shouldReceive('get')->with('TransactionManager')->andReturn(m::mock(TransactionManagerInterface::class));
        $container->shouldReceive('get')->with('CommandHandlerManager')->andReturn(m::mock(CommandHandlerManager::class));
        $container->shouldReceive('get')->with('QueryHandlerManager')->andReturn(m::mock(QueryHandlerManager::class));
        $container->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn(m::mock(IdentityProviderInterface::class));

        $sut = new SweepStaleDocumentAnalysis();
        $sut = $sut->__invoke($container, null);

        $this->analysisRepo->shouldReceive('fetchStalePending')->once()->andReturn([]);
        $this->analysisRepo->shouldReceive('sweepStalePending')->once()->andReturn(0);

        $result = $sut->handleCommand(Cmd::create([]));

        $this->assertContains(
            'Swept 0 stale document analysis row(s) to TIMEOUT (threshold 60 minutes)',
            $result->getMessages()
        );
    }
}
