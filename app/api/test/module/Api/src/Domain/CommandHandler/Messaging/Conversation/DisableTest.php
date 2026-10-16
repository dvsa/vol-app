<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation\Disable as DisableCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Disable as DisableCommand;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class DisableTest extends AbstractCommandHandlerTestCase
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
        $mockCommand = DisableCommand::create(['organisation' => 123]);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->expects('setIsMessagingDisabled')->with(true);
        $mockOrganisation->expects('getId')->withNoArgs()->andReturn(123);
        $this->expectedOrganisationCacheClear($mockOrganisation);

        $this->repoMap[OrganisationRepo::class]
            ->expects('fetchById')
            ->with(123)
            ->andReturn($mockOrganisation);
        $this->repoMap[OrganisationRepo::class]
            ->expects('save')
            ->with($mockOrganisation);

        $result = $this->sut->handleCommand($mockCommand);

        $this->assertEquals(123, $result->getId('organisation'));
        $this->assertEquals(['Messaging disabled'], $result->getMessages());
    }
}
