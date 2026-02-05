<?php

declare(strict_types=1);

/**
 * ReviveApplication.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Licence\Grant;
use Dvsa\Olcs\Api\Domain\Command\Licence\UnderConsideration;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\ReviveApplication as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\ReviveApplication as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Revive Application Test
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class ReviveApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommandWithNtu(): void
    {
        $status = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->once()
            ->andReturn(Application::APPLICATION_STATUS_NOT_TAKEN_UP);

        $command = Command::create(['id' => 532]);

        $licence = m::mock(Licence::class)
            ->shouldReceive('getId')
            ->andReturn(123);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(1);
        $application->setLicence($licence->getMock());

        $application->shouldReceive('getStatus')
            ->andReturn(
                $status->getMock()
            );

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(532)
            ->andReturn($application);

        $this->repoMap['Application']->shouldReceive('save')
            ->once()
            ->with(m::type(Application::class));

        $grantResult = new Result();
        $this->expectedSideEffect(Grant::class, ['id' => 123], $grantResult);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Application 1 has been revived"], $result->getMessages());
    }

    public function testHandleCommandWithWithdrawn(): void
    {
        $status = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->once()
            ->andReturn(Application::APPLICATION_STATUS_WITHDRAWN);

        $command = Command::create(['id' => 532]);

        $licence = m::mock(Licence::class)
            ->shouldReceive('getId')
            ->andReturn(123);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(1);
        $application->setLicence($licence->getMock());

        $application->shouldReceive('getStatus')
            ->andReturn(
                $status->getMock()
            );

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(532)
            ->andReturn($application);

        $this->repoMap['Application']->shouldReceive('save')
            ->once()
            ->with(m::type(Application::class));

        $considerationResult = new Result();
        $this->expectedSideEffect(UnderConsideration::class, ['id' => 123], $considerationResult);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Application 1 has been revived"], $result->getMessages());
    }
}
