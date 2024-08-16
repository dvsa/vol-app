<?php

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AbstractConsumerServicesTest extends MockeryTestCase
{
    public function testGetCommandHandlerManager()
    {
        $commandHandlerManager = m::mock(CommandHandlerManager::class);

        $sut = new AbstractConsumerServices($commandHandlerManager);

        $this->assertSame(
            $commandHandlerManager,
            $sut->getCommandHandlerManager()
        );
    }
}
