<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Olcs\Logging\Log\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Cli\Command\Batch\ProcessCommunityLicencesCommand;
use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Suspend as SuspendCommunityLic;
use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Activate as ActivateCommunityLic;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Domain\Query\CommunityLic\CommunityLicencesForSuspensionList;
use Dvsa\Olcs\Cli\Domain\Query\CommunityLic\CommunityLicencesForActivationList;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProcessCommunityLicencesCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private CommandHandlerManager $mockCommandHandlerManager;
    private QueryHandlerManager $mockQueryHandlerManager;

    protected function setUp(): void
    {
        $this->mockCommandHandlerManager = $this->createMock(CommandHandlerManager::class);
        $this->mockQueryHandlerManager = $this->createMock(QueryHandlerManager::class);

        $command = new ProcessCommunityLicencesCommand($this->mockCommandHandlerManager, $this->mockQueryHandlerManager);
        $application = new Application();
        $application->add($command);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->commandTester = new CommandTester($application->find('batch:process-community-licences'));
    }

    public function testExecuteWithDryRun(): void
    {
        $this->mockQueryHandlerManager->expects($this->exactly(2))
            ->method('handleQuery')
            ->willReturnOnConsecutiveCalls(
                ['count' => 1, 'result' => [['id' => 'suspensionId1']]],
                ['count' => 1, 'result' => [['id' => 'activationId1']]]
            );

        $this->mockCommandHandlerManager->expects($this->never())->method('handleCommand');

        $this->commandTester->execute(['--dry-run' => true, '-vv' => true]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteSuspensionAndActivation(): void
    {
        $this->mockQueryHandlerManager->method('handleQuery')
            ->willReturnCallback(function ($query) {
                if ($query instanceof CommunityLicencesForSuspensionList) {
                    return ['count' => 1, 'result' => [['id' => 'suspensionId1']]];
                } elseif ($query instanceof CommunityLicencesForActivationList) {
                    return ['count' => 1, 'result' => [['id' => 'activationId1']]];
                }
                return ['count' => 0, 'result' => []];
            });
        $matcher = $this->exactly(2);

        $this->mockCommandHandlerManager->expects($matcher)
            ->method('handleCommand')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame($this->isInstanceOf(SuspendCommunityLic::class), $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($this->isInstanceOf(ActivateCommunityLic::class), $parameters[0]);
                }
                return new Result();
            });

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
