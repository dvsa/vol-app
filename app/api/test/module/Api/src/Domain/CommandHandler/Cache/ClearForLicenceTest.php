<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForLicence as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cache\ClearForLicence as Handler;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Test clearing the cache for a licence
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ClearForLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $licId = 999;
        $licNo = 'OB1234567';

        $commandParams = [
            'id' => $licId,
        ];

        $command = Cmd::create($commandParams);

        $mockLicence = m::mock(LicenceEntity::class);
        $mockLicence->expects('getId')->andReturn($licId);
        $mockLicence->expects('getLicNo')->andReturn($licNo);
        $mockLicence = $this->expectedLicenceCacheClear($mockLicence);

        $this->repoMap['Licence']->expects('fetchById')->with($licId)->andReturn($mockLicence);

        $result = $this->sut->handleCommand($command);

        $expectedMessage = 'Cache cleared for Lic No ' . $licNo . ', ID ' . $licId;
        $this->assertEquals([$expectedMessage], $result->getMessages());
    }
}
