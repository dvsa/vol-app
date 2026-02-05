<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Query\Application\NotTakenUpList;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Transfer\Command\Application\NotTakenUpApplication;
use Dvsa\Olcs\Cli\Command\Batch\ProcessNtuCommand;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;

class ProcessNtuCommandTest extends TestCase
{
    private $command;
    private $mockCommandHandlerManager;
    private $mockQueryHandlerManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCommandHandlerManager = $this->createMock(CommandHandlerManager::class);
        $this->mockQueryHandlerManager = $this->createMock(QueryHandlerManager::class);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->command = new ProcessNtuCommand($this->mockCommandHandlerManager, $this->mockQueryHandlerManager);
        $this->command->setName('batch:process-ntu');
    }

    public function testExecuteWithDryRun(): void
    {
        $fakeResult = ['result' => [['id' => 1], ['id' => 2]]];
        $this->mockQueryHandlerManager->expects($this->once())
            ->method('handleQuery')
            ->with($this->equalTo(NotTakenUpList::create(['date' => (new DateTime())->format('Y-m-d')])))
            ->willReturn($fakeResult);

        $this->mockCommandHandlerManager->expects($this->never())
            ->method('handleCommand');

        $input = new ArrayInput(['--dry-run' => true], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }

    public function testExecuteWithoutDryRun(): void
    {
        $fakeResult = ['result' => [['id' => 1], ['id' => 2]]];
        $this->mockQueryHandlerManager->expects($this->once())
            ->method('handleQuery')
            ->willReturn($fakeResult);
        $matcher = $this->exactly(count($fakeResult['result']));

        $this->mockCommandHandlerManager->expects($matcher)
            ->method('handleCommand')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertEquals(NotTakenUpApplication::create(['id' => 1]), $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertEquals(NotTakenUpApplication::create(['id' => 2]), $parameters[0]);
                }
                return new Result();
            });

        $input = new ArrayInput([], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }

    public function testExecuteNoApplicationsFound(): void
    {
        $this->mockQueryHandlerManager->expects($this->once())
            ->method('handleQuery')
            ->willReturn([]);

        $this->mockCommandHandlerManager->expects($this->never())
            ->method('handleCommand');

        $input = new ArrayInput([], $this->command->getDefinition());
        $output = new BufferedOutput();
        $this->command->run($input, $output);
    }
}
