<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\EnableFileUpload as EnableCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Messaging\EnableFileUpload as EnableCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class EnableFileUpload extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EnableCommandHandler();
        $this->mockRepo(OrganisationRepo::class, OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $mockCommand = EnableCommand::create(['organisation' => 1]);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('setIsMessagingFileUploadEnabled')
                         ->once()
                         ->with(true);
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
