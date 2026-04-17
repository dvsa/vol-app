<?php

declare(strict_types=1);

/**
 * Approve Waive Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\ApproveWaive;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Transfer\Command\Fee\ApproveWaive as ApproveWaiveCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Approve Waive Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApproveWaiveTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ApproveWaive();
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            FeeEntity::STATUS_PAID,
            TransactionEntity::STATUS_PAID,
        ];

        parent::initReferences();
    }

    public function testHandleCommandApproveWaive(): void
    {
        $feeId = 69;
        $transactionId = 99;

        $command = ApproveWaiveCmd::create(
            [
                'id' => $feeId,
                'version' => 1,
                'waiveReason' => 'foo',
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
            ->with($this->mapRefdata(TransactionEntity::STATUS_PAID))
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setComment')
            ->with('foo')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setCompletedDate')
            ->with(
                m::on(
                    // compare date objects
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

        $result1 = new Result();
        $expectedData = ['id' => $feeId];
        $this->expectedSideEffect(PayFeeCmd::class, $expectedData, $result1);

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
                'Waive transaction updated',
            ],
            $result->getMessages()
        );

        $this->assertEquals($this->mapRefdata(FeeEntity::STATUS_PAID), $fee->getFeeStatus());
    }

    public function testHandleCommandApproveWaiveError(): void
    {
        $feeId = 69;

        $command = ApproveWaiveCmd::create(
            [
                'id' => $feeId,
                'version' => 1,
                'waiveReason' => 'foo',
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
