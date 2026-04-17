<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdateCheckAnswers;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCheckAnswers as UpdateCheckAnswersCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateCheckAnswersTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateCheckAnswers();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpApplicationId = 1;

        $cmdData = [
            'id' => $irhpApplicationId,
        ];

        $command = UpdateCheckAnswersCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse()
            ->shouldReceive('updateCheckAnswers')
            ->withNoArgs()
            ->once()
            ->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationId);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irhpApplicationEntity)
            ->shouldReceive('save')
            ->with($irhpApplicationEntity)
            ->once();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->never();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'IrhpApplication' => $irhpApplicationId,
            ],
            'messages' => [
                'Check Answers updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleQueryForBilateral(): void
    {
        $irhpApplicationId = 1;
        $irhpPermitApplicationId = 100;

        $cmdData = [
            'id' => $irhpApplicationId,
            'irhpPermitApplication' => $irhpPermitApplicationId,
        ];

        $command = UpdateCheckAnswersCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue()
            ->shouldReceive('updateCheckAnswers')
            ->never();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irhpApplicationEntity)
            ->shouldReceive('save')
            ->never();

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturn($irhpApplicationEntity)
            ->shouldReceive('updateCheckAnswers')
            ->withNoArgs()
            ->once()
            ->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpPermitApplicationId);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->with($irhpPermitApplicationId)
            ->andReturn($irhpPermitApplicationEntity)
            ->shouldReceive('save')
            ->with($irhpPermitApplicationEntity)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'IrhpPermitApplication' => $irhpPermitApplicationId,
            ],
            'messages' => [
                'Check Answers updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleQueryForBilateralMismatchedIds(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Mismatched IrhpApplication and IrhpPermitApplication');

        $irhpApplicationId = 1;
        $irhpPermitApplicationId = 100;

        $cmdData = [
            'id' => $irhpApplicationId,
            'irhpPermitApplication' => $irhpPermitApplicationId,
        ];

        $command = UpdateCheckAnswersCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue()
            ->shouldReceive('updateCheckAnswers')
            ->never();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irhpApplicationEntity)
            ->shouldReceive('save')
            ->never();

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturnNull()
            ->shouldReceive('updateCheckAnswers')
            ->never();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->with($irhpPermitApplicationId)
            ->andReturn($irhpPermitApplicationEntity)
            ->shouldReceive('save')
            ->never();

        $this->sut->handleCommand($command);
    }
}
