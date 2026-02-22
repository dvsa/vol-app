<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Organisation;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Organisation\FixIsUnlicenced;
use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsIrfo as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;

/**
 * Class FixIsUnlicencedTest
 */
class FixIsUnlicencedTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FixIsUnlicenced();
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create([]);

        $this->repoMap['Organisation']->shouldReceive('fixIsUnlicenced')->with()->once()->andReturn(45);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['45 organisation(s) changed to isUnlicenced = 0'], $result->getMessages());
    }
}
