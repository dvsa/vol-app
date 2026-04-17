<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\Expire as ExpireIrhpApplication;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit\Terminate as CmdHandler;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Terminate;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Terminate IRHP Permit Test
 *
 * @author Tonci Vidovic <Tonci.vidovic@capgemini.com>
 */
class TerminateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CmdHandler();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpPermit::STATUS_TERMINATED,
        ];

        parent::initReferences();
    }

    public function testCatchForbiddenException(): void
    {
        $permitId = 9;

        $permit = m::mock(IrhpPermit::class)->makePartial();

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($permitId)
            ->once()
            ->andReturn($permit);

        $permit->shouldReceive('proceedToStatus')
            ->with($this->refData[IrhpPermit::STATUS_TERMINATED])
            ->once()
            ->andThrow(new ForbiddenException());

        $this->repoMap['IrhpPermit']
            ->shouldReceive('save')
            ->with($permit)
            ->never();

        $command = Terminate::create(['id' => $permitId]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'messages' => ['You cannot terminate an inactive permit.'],
            'id' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testTerminatePermit(): void
    {
        $permitId = 9;

        $permit = m::mock(IrhpPermit::class)->makePartial();
        $permit->setId($permitId);
        $permit->setStatus(new RefData(IrhpPermit::STATUS_PENDING));

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($permitId)
            ->once()
            ->andReturn($permit);

        $permit->shouldReceive('proceedToStatus')
            ->with($this->refData[IrhpPermit::STATUS_TERMINATED])
            ->once();

        $this->repoMap['IrhpPermit']
            ->shouldReceive('save')
            ->with($permit)
            ->once();

        $permit->shouldReceive('getIrhpPermitApplication->getIrhpApplication->canBeExpired')
            ->andReturn(false);

        $command = Terminate::create(['id' => $permitId]);
        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'messages' => ['The selected permit has been terminated.'],
                'id' => ['IrhpPermit' => $permitId]
            ],
            $result->toArray()
        );
    }

    public function testTerminateLastPermitOnIrhpApp(): void
    {
        $permitId = 9;

        $permit = m::mock(IrhpPermit::class)->makePartial();
        $permit->setId($permitId);
        $permit->setStatus(new RefData(IrhpPermit::STATUS_PENDING));

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($permitId)
            ->once()
            ->andReturn($permit);

        $permit->shouldReceive('proceedToStatus')
            ->with($this->refData[IrhpPermit::STATUS_TERMINATED])
            ->once();

        $this->repoMap['IrhpPermit']
            ->shouldReceive('save')
            ->with($permit)
            ->once();

        $appId = 1;
        $application = m::mock(IrhpApplication::class);
        $application->shouldReceive('getId')
            ->andReturn($appId);
        $application->shouldReceive('canBeExpired')
            ->andReturn(true);

        $permit->shouldReceive('getIrhpPermitApplication->getIrhpApplication')
            ->andReturn($application);

        $this->expectedSideEffect(
            ExpireIrhpApplication::class,
            [
                'id' => $appId,
            ],
            new Result()
        );

        $command = Terminate::create(['id' => $permitId]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'messages' => ['The selected permit has been terminated.'],
            'id' => ['IrhpPermit' => $permitId]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
