<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si\Applied;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\Applied\Delete as DeletePenalty;
use Dvsa\Olcs\Api\Domain\Repository\SiPenalty as SiPenaltyRepo;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\Applied\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Test
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeletePenalty();
        $this->mockRepo('SiPenalty', SiPenaltyRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $penaltyId = 111;

        $command = Cmd::create(
            [
                'id' => $penaltyId,
            ]
        );

        $penaltyEntity = m::mock(SiPenaltyEntity::class)->makePartial();
        $penaltyEntity->shouldReceive('getId')->once()->andReturn($penaltyId);
        $penaltyEntity->shouldReceive('getSeriousInfringement->getCase->isOpenErruCase')->once()->andReturn(true);

        $this->repoMap['SiPenalty']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($penaltyEntity);
        $this->repoMap['SiPenalty']->shouldReceive('delete')
            ->with(m::type(SiPenaltyEntity::class))
            ->once();

        $expected = [
            'id' => [
                'id' => $penaltyId
            ],
            'messages' => [
                'Applied penalty deleted'
            ]
        ];

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandThrowsExceptionWhenNotOpenErruCase(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $penaltyId = 111;

        $command = Cmd::create(
            [
                'id' => $penaltyId,
            ]
        );

        $penaltyEntity = m::mock(SiPenaltyEntity::class)->makePartial();
        $penaltyEntity->shouldReceive('getSeriousInfringement->getCase->isOpenErruCase')->once()->andReturn(false);

        $this->repoMap['SiPenalty']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($penaltyEntity);

        $this->sut->handleCommand($command);
    }
}
