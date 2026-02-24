<?php

declare(strict_types=1);

/**
 * Recommend Waive Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\RecommendWaive;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Transfer\Command\Fee\RecommendWaive as RecommendWaiveCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Recommend Waive Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RecommendWaiveTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RecommendWaive();
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            FeeEntity::METHOD_WAIVE,
            TransactionEntity::TYPE_WAIVE,
            TransactionEntity::STATUS_OUTSTANDING,
        ];

        parent::initReferences();
    }

    public function testHandleCommandRecommendWaive(): void
    {
        $feeId = 69;

        $command = RecommendWaiveCmd::create(
            [
                'id' => $feeId,
                'version' => 1,
                'waiveReason' => 'foo',
            ]
        );

        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setId($feeId);
        $fee->setFeeTransactions(new ArrayCollection());

        $user = m::mock();
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);
        $now = new DateTime();

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
                'transaction' => null, // we haven't mocked this id
            ],
            $result->getIds()
        );
        $this->assertEquals(
            [
                'Fee updated',
                'Waive transaction created',
            ],
            $result->getMessages()
        );

        $transaction = $fee->getFeeTransactions()->first()->getTransaction();
        $this->assertSame($this->mapRefdata(TransactionEntity::TYPE_WAIVE), $transaction->getType());
        $this->assertSame($this->mapRefdata(TransactionEntity::STATUS_OUTSTANDING), $transaction->getStatus());
        $this->assertSame($this->mapRefdata(FeeEntity::METHOD_WAIVE), $transaction->getPaymentMethod());
        $this->assertEquals('foo', $transaction->getComment());
        $this->assertEquals($now->format('Y-m-d'), $transaction->getWaiveRecommendationDate()->format('Y-m-d'));
        $this->assertSame($user, $transaction->getWaiveRecommenderUser());
    }
}
