<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForOrganisation as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cache\ClearForOrganisation as Handler;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Test clearing the cache for an organisation
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ClearForOrganisationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $orgId = 999;
        $orgName = 'org name';

        $commandParams = [
            'id' => $orgId,
        ];

        $command = Cmd::create($commandParams);

        $mockOrg = m::mock(OrganisationEntity::class);
        $mockOrg->expects('getId')->andReturn($orgId);
        $mockOrg->expects('getName')->andReturn($orgName);
        $mockOrg = $this->expectedOrganisationCacheClear($mockOrg);

        $this->repoMap['Organisation']->expects('fetchById')->with($orgId)->andReturn($mockOrg);

        $result = $this->sut->handleCommand($command);

        $expectedMessage = 'Cache cleared for ' . $orgName . ', Organisation ID ' . $orgId;
        $this->assertEquals([$expectedMessage], $result->getMessages());
    }
}
