<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AbstractConsumerServicesTest extends MockeryTestCase
{
    public function testGetCommandHandlerManager(): void
    {
        $commandHandlerManager = m::mock(CommandHandlerManager::class);

        $sut = new AbstractConsumerServices($commandHandlerManager);

        $this->assertSame(
            $commandHandlerManager,
            $sut->getCommandHandlerManager()
        );
    }
}
