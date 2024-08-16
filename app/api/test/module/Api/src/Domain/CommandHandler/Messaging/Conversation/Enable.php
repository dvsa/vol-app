<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation\Enable as EnableCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Enable as EnableCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class Enable extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EnableCommandHandler();
        $this->mockRepo(OrganisationRepo::class, OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $mockCommand = EnableCommand::create(['organisation' => 111]);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('setIsMessagingDisabled')
                         ->once()
                         ->with(false);
        $mockOrganisation->shouldReceive('getId')
                         ->twice()
                         ->andReturn(111);

        $this->repoMap[OrganisationRepo::class]
            ->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andReturn($mockOrganisation);
        $this->repoMap[OrganisationRepo::class]
            ->shouldReceive('save')
            ->once()
            ->with($mockOrganisation);

        $result = $this->sut->handleCommand($mockCommand);

        $this->assertEquals(111, $result->getId('organisation'));
    }
}
