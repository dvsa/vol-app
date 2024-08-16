<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation\Disable as DisableCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Disable as DisableCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class Disable extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DisableCommandHandler();
        $this->mockRepo(OrganisationRepo::class, OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $mockCommand = DisableCommand::create(['organisation' => 123]);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('setIsMessagingDisabled')
                         ->once()
                         ->with(true);
        $mockOrganisation->shouldReceive('getId')
                         ->atLeast()
                         ->andReturn(123);

        $this->repoMap[OrganisationRepo::class]
            ->shouldReceive('fetchById')
            ->atLeast()
            ->with(123)
            ->andReturn($mockOrganisation);
        $this->repoMap[OrganisationRepo::class]
            ->shouldReceive('save')
            ->once()
            ->with($mockOrganisation);

        $result = $this->sut->handleCommand($mockCommand);

        $this->assertEquals(123, $result->getId('organisation'));
    }
}
