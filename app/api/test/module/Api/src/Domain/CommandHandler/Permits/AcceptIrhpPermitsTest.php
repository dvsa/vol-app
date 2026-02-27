<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\AcceptIrhpPermits;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AcceptIrhpPermitsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->sut = new AcceptIrhpPermits();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_ISSUING
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $irhpApplicationId = 54;

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('proceedToIssuing')
            ->with($this->refData[IrhpInterface::STATUS_ISSUING])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);
        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->expectedQueueSideEffect($irhpApplicationId, Queue::TYPE_IRHP_APPLICATION_PERMITS_ALLOCATE, []);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            $irhpApplicationId,
            $result->getId('irhpApplication')
        );

        $this->assertEquals(
            ['Queued allocation of permits'],
            $result->getMessages()
        );
    }

    public function testHandleCommandIssuingFailed(): void
    {
        $irhpApplicationId = 54;

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('proceedToIssuing')
            ->with($this->refData[IrhpInterface::STATUS_ISSUING])
            ->andThrow(new ForbiddenException());

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);
        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->never();

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $this->sut->handleCommand($command);
    }
}
