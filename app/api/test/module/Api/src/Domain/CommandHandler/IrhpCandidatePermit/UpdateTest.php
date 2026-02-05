<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpCandidatePermit;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpCandidatePermit\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Update as UpdateCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Update Irhp Candidate Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);
        $this->mockRepo('IrhpPermitRange', IrhpPermitRangeRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 1;
        $cmdData = [
            'irhpPermitRange' => '22',
            'irhpPermitApplication' => '77',
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(IrhpCandidatePermitEntity::class);
        $range = m::mock(IrhpPermitRangeEntity::class);

        $this->repoMap['IrhpCandidatePermit']
            ->shouldReceive('fetchById')
            ->with($command->getId())
            ->andReturn($entity);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchById')
            ->with($command->getIrhpPermitRange())
            ->andReturn($range);

        $range->shouldReceive('getIrhpPermitStock->getId')
            ->once()
            ->withNoArgs()
            ->andReturn(44);

        $entity->shouldReceive('getIrhpPermitRange->getIrhpPermitStock->getId')
            ->once()
            ->withNoArgs()
            ->andReturn(44);

        $entity->shouldReceive('getId')
            ->once()
            ->andReturn($id);

        $entity->shouldReceive('updateIrhpPermitRange')
            ->with($range)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']
            ->shouldReceive('save')
            ->once()
            ->globally()
            ->ordered()
            ->with($entity);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['irhpCandidatePermit' => $id],
            'messages' => ['IRHP Candidate Permit Updated']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandBadRange(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $cmdData = [
            'irhpPermitRange' => '22',
            'irhpPermitApplication' => '77',
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(IrhpCandidatePermitEntity::class);
        $range = m::mock(IrhpPermitRangeEntity::class);

        $this->repoMap['IrhpCandidatePermit']
            ->shouldReceive('fetchById')
            ->with($command->getId())
            ->andReturn($entity);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchById')
            ->with($command->getIrhpPermitRange())
            ->andReturn($range);

        $range->shouldReceive('getIrhpPermitStock->getId')
            ->once()
            ->withNoArgs()
            ->andReturn(44);

        $entity->shouldReceive('getIrhpPermitRange->getIrhpPermitStock->getId')
            ->once()
            ->withNoArgs()
            ->andReturn(48);

        $this->sut->handleCommand($command);
    }
}
