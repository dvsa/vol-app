<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\DisableFileUpload as DisableCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Messaging\DisableFileUpload as DisableCommand;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class DisableFileUploadTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DisableCommandHandler();
        $this->mockRepo(OrganisationRepo::class, OrganisationRepo::class);
        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $mockCommand = DisableCommand::create(['organisation' => 1]);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->expects('setIsMessagingFileUploadEnabled')->with(false);
        $mockOrganisation->expects('getId')->withNoArgs()->andReturn(1);
        $this->expectedOrganisationCacheClear($mockOrganisation);

        $this->repoMap[OrganisationRepo::class]
            ->expects('fetchById')
            ->with(1)
            ->andReturn($mockOrganisation);
        $this->repoMap[OrganisationRepo::class]
            ->expects('save')
            ->with($mockOrganisation);

        $result = $this->sut->handleCommand($mockCommand);

        $this->assertEquals(1, $result->getId('organisation'));
        $this->assertEquals(['File upload disabled'], $result->getMessages());
    }
}
