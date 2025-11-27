<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\ProcessPack;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\ProcessPackException;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPack as ProcessPackCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackTransaction as ProcessPackTransactionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;

class ProcessPackTest extends ProcessPackTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessPack();
        parent::setUp();
    }

    public function testHandleCommand()
    {
        $cmdData = [
            'organisation' => 11,
            'id' => 12
        ];

        $command = ProcessPackCmd::create($cmdData);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(ProcessPackTransactionCmd::class, false)
            ->andReturn(new Result());

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithException()
    {
        $cmdData = [
            'organisation' => 11,
            'id' => 12
        ];

        $command = ProcessPackCmd::create($cmdData);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(ProcessPackTransactionCmd::class, false)
            ->andThrow(\Exception::class);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(CreateQueue::class, false)
            ->andReturn(new Result());

        $this->expectException(ProcessPackException::class);

        $this->sut->handleCommand($command);
    }
}
