<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\CancelableInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

abstract class AbstractCancelApplicationHandlerTestCase extends AbstractCommandHandlerTestCase
{
    protected $repoServiceName = 'changeMe';
    protected $entityClass = 'changeMe';
    protected $repoClass = 'changeMe';
    protected $sutClass = 'changeMe';
    protected $cancelStatus = 'changeMe';

    public function setUp(): void
    {
        $this->mockRepo($this->repoServiceName, $this->repoClass);
        $this->sut = new $this->sutClass();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            $this->cancelStatus
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $applicationId = 4096;
        $feeId1 = 111;
        $feeId2 = 222;

        $application = m::mock(CancelableInterface::class);
        $application->shouldReceive('cancel')
            ->with($this->mapRefData($this->cancelStatus))
            ->once()
            ->globally()
            ->ordered();

        $application->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn($applicationId);

        $command = m::mock(CommandInterface::class);

        $fee1 = m::mock(Fee::class);
        $fee1->shouldReceive('getId')->once()->withNoArgs()->andReturn($feeId1);
        $fee2 = m::mock(Fee::class);
        $fee2->shouldReceive('getId')->once()->withNoArgs()->andReturn($feeId2);
        $fees = [$fee1, $fee2];

        $application->shouldReceive('getOutstandingFees')->once()->withNoArgs()->andReturn($fees);

        $this->expectedSideEffect(
            CancelFee::class,
            [ 'id' => $feeId1],
            new Result()
        );

        $this->expectedSideEffect(
            CancelFee::class,
            [ 'id' => $feeId2],
            new Result()
        );

        $this->repoMap[$this->repoServiceName]->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($application);

        $this->repoMap[$this->repoServiceName]->shouldReceive('save')
            ->with($application)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($applicationId, $result->getId($this->repoServiceName));
        $this->assertEquals([$this->repoServiceName . ' cancelled'], $result->getMessages());
    }
}
