<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\WithdrawApplicationInterface;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

abstract class AbstractWithdrawApplicationHandlerTestCase extends AbstractCommandHandlerTestCase
{
    protected $repoServiceName = 'changeMe';
    protected $entityClass = 'changeMe';
    protected $repoClass = 'changeMe';
    protected $sutClass = 'changeMe';
    protected $withdrawStatus = IrhpInterface::STATUS_WITHDRAWN;
    protected $emails = []; //map a withdraw status to a confirmation email

    public function setUp(): void
    {
        $this->mockRepo($this->repoServiceName, $this->repoClass);
        $this->sut = new $this->sutClass();

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS,
            WithdrawableInterface::WITHDRAWN_REASON_BY_USER,
            WithdrawableInterface::WITHDRAWN_REASON_UNPAID,
            WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
            WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED,
            $this->withdrawStatus,
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpReasonProvider')]
    public function testHandleCommandWithEmail(mixed $withdrawReason, mixed $isInternalUser, mixed $expectedCheckReasonAgainstStatus): void
    {
        $id = 4096;
        $feeId1 = 111;
        $feeId2 = 222;

        $emailCommand = 'Email\Command';

        $this->setupIsInternalUser($isInternalUser);

        $application = m::mock(WithdrawableInterface::class);
        $application->shouldReceive('withdraw')
            ->with(
                $this->mapRefData($this->withdrawStatus),
                $this->mapRefData($withdrawReason),
                $expectedCheckReasonAgainstStatus
            )
            ->once()
            ->globally()
            ->ordered();
        $application->shouldReceive('getAppWithdrawnEmailCommand')
            ->with($withdrawReason)
            ->once()
            ->andReturn($emailCommand);

        $command = m::mock(WithdrawApplicationInterface::class);
        $command->shouldReceive('getId')->andReturn($id);
        $command->shouldReceive('getReason')->withNoArgs()->andReturn($withdrawReason);

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

        $this->expectedEmailQueueSideEffect($emailCommand, ['id' => $id], $id, new Result());

        $this->repoMap[$this->repoServiceName]->shouldReceive('fetchById')
            ->once()
            ->with($id)
            ->andReturn($application);

        $this->repoMap[$this->repoServiceName]->shouldReceive('save')
            ->with($application)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($id, $result->getId($this->repoServiceName));
        $this->assertEquals(['Application withdrawn'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpReasonProvider')]
    public function testHandleCommandWithoutEmail(mixed $withdrawReason, mixed $isInternalUser, mixed $expectedCheckReasonAgainstStatus): void
    {
        $id = 4096;
        $feeId1 = 111;
        $feeId2 = 222;

        $this->setupIsInternalUser($isInternalUser);

        $application = m::mock(WithdrawableInterface::class);
        $application->shouldReceive('withdraw')
            ->with(
                $this->mapRefData($this->withdrawStatus),
                $this->mapRefData($withdrawReason),
                $expectedCheckReasonAgainstStatus
            )
            ->once()
            ->globally()
            ->ordered();
        $application->shouldReceive('getAppWithdrawnEmailCommand')
            ->with($withdrawReason)
            ->andReturnNull();

        $command = m::mock(WithdrawApplicationInterface::class);
        $command->shouldReceive('getId')->andReturn($id);
        $command->shouldReceive('getReason')->withNoArgs()->andReturn($withdrawReason);

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

        $this->repoMap[$this->repoServiceName]->shouldReceive('fetchById')
            ->once()
            ->with($id)
            ->andReturn($application);

        $this->repoMap[$this->repoServiceName]->shouldReceive('save')
            ->with($application)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($id, $result->getId($this->repoServiceName));
        $this->assertEquals(['Application withdrawn'], $result->getMessages());
    }

    public static function dpReasonProvider(): array
    {
        return [
            [WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS, false, true],
            [WithdrawableInterface::WITHDRAWN_REASON_BY_USER, false, true],
            [WithdrawableInterface::WITHDRAWN_REASON_DECLINED, false, true],
            [WithdrawableInterface::WITHDRAWN_REASON_UNPAID, false, true],
            [WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED, false, true],
            [WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS, true, false],
            [WithdrawableInterface::WITHDRAWN_REASON_BY_USER, true, false],
            [WithdrawableInterface::WITHDRAWN_REASON_DECLINED, true, false],
            [WithdrawableInterface::WITHDRAWN_REASON_UNPAID, true, false],
            [WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED, true, false],
        ];
    }
}
