<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\CommandHandler\CommandHandlerInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\HandlerInterface;
use Dvsa\Olcs\Api\Domain\ValidationHandlerManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

class CommandHandlerManagerTest extends MockeryTestCase
{
    /**
     * @var CommandHandlerManager
     */
    private $sut;

    private $vhm;

    public function setUp(): void
    {
        $this->vhm = m::mock(ValidationHandlerManager::class)->makePartial();
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('ValidationHandlerManager')->andReturn($this->vhm);

        $this->sut = new CommandHandlerManager($container, []);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    public function testHandleCommand(): void
    {
        $command = m::mock(CommandInterface::class)->makePartial();
        $command->shouldReceive('getArrayCopy')->once()->andReturn(['foo' => 'bar']);

        $mockService = m::mock(CommandHandlerInterface::class);
        $mockService->shouldReceive('handleCommand')->with($command)->andReturn(['response']);
        $mockService->shouldReceive('checkEnabled')->once()->andReturn(true);

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($command)->andReturn(true);
        $this->vhm->setService($mockService::class, $mockValidator);

        $this->sut->setService($command::class, $mockService);

        $this->assertEquals(['response'], $this->sut->handleCommand($command));
    }

    public function testHandleCommandWithWrapped(): void
    {
        $command = m::mock(CommandInterface::class)->makePartial();
        $command->shouldReceive('getArrayCopy')->once()->andReturn(['foo' => 'bar']);

        $wrapped = m::mock(CommandHandlerInterface::class);
        $wrapped->shouldReceive('checkEnabled')->once()->andReturn(true);

        $mockService = m::mock(TransactioningCommandHandler::class);
        $mockService->shouldReceive('getWrapped')->andReturn($wrapped);
        $mockService->shouldReceive('handleCommand')->with($command)->andReturn(['response']);

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($command)->andReturn(true);
        $this->vhm->setService($wrapped::class, $mockValidator);

        $this->sut->setService($command::class, $mockService);

        $this->assertEquals(['response'], $this->sut->handleCommand($command));
    }

    public function testHandleCommandFailedValidator(): void
    {
        $this->expectException(ForbiddenException::class);

        $command = m::mock(CommandInterface::class)->makePartial();
        $command->shouldReceive('getArrayCopy')->twice()->andReturn(['foo' => 'bar']);

        $mockService = m::mock(CommandHandlerInterface::class);
        $mockService->shouldReceive('handleCommand')->never();
        $mockService->shouldReceive('checkEnabled')->once()->andReturn(true);

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($command)->andReturn(false);
        $this->vhm->setService($mockService::class, $mockValidator);

        $this->sut->setService($command::class, $mockService);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $command = m::mock(CommandInterface::class)->makePartial();

        $mockService = m::mock();
        $mockService->shouldReceive('handleCommand')->with($command)->andReturn(['response']);

        $this->sut->setService($command::class, $mockService);

        $this->sut->handleCommand($command);
    }

    public function testValidate(): void
    {
        $plugin = m::mock(CommandHandlerInterface::class);

        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }
}
