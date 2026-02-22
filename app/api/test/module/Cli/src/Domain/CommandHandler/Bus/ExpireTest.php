<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Cli\Domain\Command\Bus\Expire as ExpireBusCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Bus\Expire as ExpireHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Bus reg expiry command handler test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ExpireTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ExpireHandler();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    /**
     * tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $numExpired = 666;
        $this->repoMap['Bus']->expects('expireRegistrations')->withNoArgs()->andReturn($numExpired);
        $result = $this->sut->handleCommand(ExpireBusCmd::create([]));

        $messages = [0 => $numExpired . ' registrations have been expired'];
        $this->assertEquals($messages, $result->getMessages());
    }
}
