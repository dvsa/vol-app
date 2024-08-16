<?php

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

        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);

        $this->commandTester = new CommandTester($application->find('batch:process-community-licences'));
    }

    public function testExecuteWithDryRun()
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

    public function testExecuteSuspensionAndActivation()
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

        $this->mockCommandHandlerManager->expects($this->exactly(2))
            ->method('handleCommand')
            ->withConsecutive(
                [$this->isInstanceOf(SuspendCommunityLic::class)],
                [$this->isInstanceOf(ActivateCommunityLic::class)]
            )
            ->willReturn(new Result());

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
