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
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

final class CommandHandlerManagerTest extends MockeryTestCase
{
    /**
     * @var CommandHandlerManager
     */
    private $sut;

    private $vhm;

    #[\Override]
    public function setUp(): void
    {
        $this->vhm = m::mock(ValidationHandlerManager::class)->makePartial();
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('ValidationHandlerManager')->andReturn($this->vhm);

        $this->sut = new CommandHandlerManager($container, []);

        Logger::setLogger(new \Psr\Log\NullLogger());
    }

    #[\Override]
    public function tearDown(): void
    {
        Logger::setLogger(new \Psr\Log\NullLogger());
        parent::tearDown();
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

    public function testHandleCommandFailedValidatorOmitsContentFromLog(): void
    {
        $this->expectException(ForbiddenException::class);

        $logger = m::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('debug')->once()->with(m::pattern('/^Command Received/'), m::type('array'));
        $logger->shouldReceive('debug')->once()->with(
            'DTO Failed validation',
            m::on(fn($context) => !str_contains(json_encode($context), 'secret') && str_contains(json_encode($context), 'OMITTED'))
        );
        Logger::setLogger($logger);

        $command = m::mock(CommandInterface::class . ', ' . LoggerOmitContentInterface::class)->makePartial();
        $command->shouldReceive('getArrayCopy')->never();

        $mockService = m::mock(CommandHandlerInterface::class);
        $mockService->shouldReceive('handleCommand')->never();
        $mockService->shouldReceive('checkEnabled')->once()->andReturn(true);

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($command)->once()->andReturn(false);
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
