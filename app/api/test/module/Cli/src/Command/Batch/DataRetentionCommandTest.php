<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\DataRetention\DeleteEntities;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\Populate;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\Precheck;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Query\DataRetention\Postcheck;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Cli\Command\Batch\DataRetentionCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Query\Result as QueryResult;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DataRetentionCommandTest extends TestCase
{
    public $mockQueryHandlerManager;
    private $command;
    private $commandTester;
    private $mockCommandHandlerManager;

    protected function setUp(): void
    {
        $this->mockCommandHandlerManager = $this->createMock(CommandHandlerManager::class);
        $this->mockQueryHandlerManager = $this->createMock(QueryHandlerManager::class);
        $this->command = new DataRetentionCommand($this->mockCommandHandlerManager, $this->mockQueryHandlerManager);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testPopulateOption(): void
    {
        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->equalTo(Populate::create([])))
            ->willReturn(new Result());

        $input = new ArrayInput(['--populate' => true], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }

    public function testDeleteOption(): void
    {
        $limit = 10;
        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->equalTo(DeleteEntities::create(['limit' => $limit])))
            ->willReturn(new Result());

        $input = new ArrayInput(['--delete' => true, '--limit' => $limit], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }

    public function testPrecheckOption(): void
    {
        $limit = 10;
        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->equalTo(Precheck::create(['limit' => $limit])))
            ->willReturn(new Result());

        $input = new ArrayInput(['--precheck' => true, '--limit' => $limit], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }

    public function testPostcheckOption(): void
    {
        $this->mockQueryHandlerManager->expects($this->once())
            ->method('handleQuery')
            ->with($this->equalTo(Postcheck::create([])))
            ->willReturn(['count' => 0, 'result' => []]);

        $input = new ArrayInput(['--postcheck' => true], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }

    public function testNoOption(): void
    {
        $this->commandTester->execute([]);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
