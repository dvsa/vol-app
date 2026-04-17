<?php

declare(strict_types=1);

/**
 * Reject Waive Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\RejectWaive;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Transfer\Command\Fee\RejectWaive as RejectWaiveCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Reject Waive Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RejectWaiveTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RejectWaive();
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            TransactionEntity::STATUS_CANCELLED,
        ];

        parent::initReferences();
    }

    public function testHandleCommandRejectWaive(): void
    {
        $feeId = 69;
        $transactionId = 99;

        $command = RejectWaiveCmd::create(
            [
                'id' => $feeId,
                'version' => 1,
            ]
        );

        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setId($feeId);

        $user = m::mock();
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);
        $now = new DateTime();

        $transaction = m::mock(TransactionEntity::class)
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(TransactionEntity::STATUS_CANCELLED))
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setCompletedDate')
            ->with(
                m::on(
                    fn($arg) => $arg == $now
                )
            )
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setProcessedByUser')
            ->with($user)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getId')
            ->andReturn($transactionId)
            ->getMock();

        $fee->shouldReceive('getOutstandingWaiveTransaction')
            ->once()
            ->andReturn($transaction);

        $this->repoMap['Fee']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->once()
            ->andReturn($fee)
            ->shouldReceive('save')
            ->with($fee)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals(
            [
                'fee' => $feeId,
                'transaction' => $transactionId,
            ],
            $result->getIds()
        );
        $this->assertEquals(
            [
                'Fee updated',
                'Waive transaction cancelled',
            ],
            $result->getMessages()
        );
    }

    public function testHandleCommandRejectWaiveError(): void
    {
        $feeId = 69;

        $command = RejectWaiveCmd::create(
            [
                'id' => $feeId,
                'version' => 1,
            ]
        );

        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setId($feeId);

        $this->repoMap['Fee']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->once()
            ->andReturn($fee);

        $fee->shouldReceive('getOutstandingWaiveTransaction')
            ->once()
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
