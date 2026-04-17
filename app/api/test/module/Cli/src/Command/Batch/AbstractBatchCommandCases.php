<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Olcs\Logging\Log\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
abstract class AbstractBatchCommandCases extends TestCase
{
    protected $commandTester;
    protected $mockCommandHandlerManager;
    protected $mockQueryHandlerManager;
    protected $hasQueries = false;

    protected $additionalArguments = [];

    protected $sut;

    /**
     * The FQCN of the command to be tested.
     */
    abstract protected function getCommandClass(): string;

    /**
     * The command name used in the console application.
     */
    abstract protected function getCommandName(): string;

    /**
     * An array of command DTOs that are expected to be handled.
     */
    abstract protected function getCommandDTOs(): array;

    protected function setUp(): void
    {
        $this->mockCommandHandlerManager = $this->createMock(CommandHandlerManager::class);
        $this->mockQueryHandlerManager = $this->createMock(QueryHandlerManager::class);

        $commandClass = $this->getCommandClass();
        $this->sut = new $commandClass($this->mockCommandHandlerManager, $this->mockQueryHandlerManager);
        $this->sut->setName($this->getCommandName());

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $application = new Application();
        $application->add($this->sut);

        $this->commandTester = new CommandTester($application->find($this->getCommandName()));
    }

    public function executeCommand(array $additionalArguments = []): void
    {
        $defaultArguments = [
            'command' => $this->getCommandName(),
        ];

        $arguments = array_merge($defaultArguments, $this->additionalArguments, $additionalArguments);

        $this->commandTester->execute($arguments);
    }

    public function testExecuteSuccess(): void
    {
        $dtos = $this->getCommandDTOs();
        $dtoCount = count($dtos);

        $this->mockCommandHandlerManager->expects($this->exactly($dtoCount))
            ->method('handleCommand')
            ->with($this->callback(function ($commandInstance) use ($dtos) {
                foreach ($dtos as $dto) {
                    if ($commandInstance == $dto) {
                        return true;
                    }
                }
                return false;
            }))
            ->willReturnCallback(fn($command) => new Result());

        $this->executeCommand();
    }


    public function testExecuteHandlesGenericException(): void
    {
        $this->mockCommandHandlerManager->method('handleCommand')
            ->will($this->throwException(new \Exception('Test exception')));

        $this->executeCommand();

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteHandlesNotFoundException(): void
    {
        $this->mockCommandHandlerManager->method('handleCommand')
            ->will($this->throwException(new \Dvsa\Olcs\Api\Domain\Exception\NotFoundException('Test not found exception')));

        $this->executeCommand();

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
