<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\DisableFileUpload as DisableCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Messaging\DisableFileUpload as DisableCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class DisableFileUpload extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DisableCommandHandler();
        $this->mockRepo(OrganisationRepo::class, OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $mockCommand = DisableCommand::create(['organisation' => 1]);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('setIsMessagingFileUploadEnabled')
                         ->once()
                         ->with(false);
        $mockOrganisation->shouldReceive('getId')
                         ->twice()
                         ->andReturn(1);

        $this->repoMap[OrganisationRepo::class]
            ->shouldReceive('fetchById')
            ->once()
            ->with(1)
            ->andReturn($mockOrganisation);
        $this->repoMap[OrganisationRepo::class]
            ->shouldReceive('save')
            ->once()
            ->with($mockOrganisation);

        $result = $this->sut->handleCommand($mockCommand);

        $this->assertEquals(1, $result->getId('organisation'));
    }
}
