<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Symfony\Component\Console\Command\Command;

trait QueryHandlerExceptionTestsTrait
{
    /**
     * Tests handling of a generic exception by the QueryHandlerManager.
     */
    public function testQueryHandlingGenericException(): void
    {
        $this->mockQueryHandlerManager->expects($this->any())
            ->method('handleQuery')
            ->willThrowException(new \Exception('Generic exception'));

        $this->executeCommand();
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    /**
     * Tests handling of a NotFoundException by the QueryHandlerManager.
     */
    public function testQueryHandlingNotFoundException(): void
    {
        $this->mockQueryHandlerManager->expects($this->any())
            ->method('handleQuery')
            ->willThrowException(new NotFoundException('Not found exception'));

        $this->executeCommand();
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteHandlesThrowable(): void
    {
        $this->mockQueryHandlerManager->method('handleQuery')
            ->will($this->throwException(new \Error('Test throwable')));
        $this->executeCommand();
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
