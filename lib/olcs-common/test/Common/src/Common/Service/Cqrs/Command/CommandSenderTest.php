<?php

namespace CommonTest\Common\Service\Cqrs\Command;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CommandSenderTest extends MockeryTestCase
{
    protected $sut;

    protected $commandService;

    protected $annotationBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new CommandSender();

        $this->commandService = m::mock();
        $this->annotationBuilder = m::mock();

        $sm = new ServiceManager();

        $sm->setService('CommandService', $this->commandService);
        $sm->setService('TransferAnnotationBuilder', $this->annotationBuilder);

        $service = $this->sut->__invoke($sm, CommandSender::class);

        $this->assertSame($service, $this->sut);
    }

    public function testSend(): void
    {
        $command = m::mock(CommandInterface::class);
        $constructedCommand = m::mock();
        $response = m::mock();

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with($command)
            ->andReturn($constructedCommand);

        $this->commandService->shouldReceive('send')
            ->once()
            ->with($constructedCommand)
            ->andReturn($response);

        $this->assertSame($response, $this->sut->send($command));
    }
}
