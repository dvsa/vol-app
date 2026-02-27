<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Doctrine\ORM\Exception\ORMException;
use DomainException;
use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Domain\Command\Queue\Complete;
use Dvsa\Olcs\Api\Domain\Command\Queue\Failed;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\RefundInterimFees;
use Dvsa\Olcs\Transfer\Command\Fee\RefundFee;
use Mockery as m;

class RefundInterimFeesTest extends AbstractConsumerTestCase
{
    public const QUEUE_ITEM_ENTITY_ID = 17;

    /**
     * @var QueueEntity
     */
    private $item;

    /**
     * @var FeeEntity
     */
    private $fee;

    /**
     * @var FeeRepo
     */
    private $feeRepo;

    public function setUp(): void
    {
        $this->item = new QueueEntity();
        $this->item->setId(1);
        $this->item->setEntityId(self::QUEUE_ITEM_ENTITY_ID);

        $this->fee = m::mock(FeeEntity::class);

        parent::setUp();
    }

    #[\Override]
    protected function instantiate(): void
    {
        $this->feeRepo = m::mock(FeeRepo::class);
        $this->feeRepo->shouldReceive('fetchById')
            ->with(self::QUEUE_ITEM_ENTITY_ID)
            ->andReturn($this->fee);

        $this->sut = new RefundInterimFees(
            $this->abstractConsumerServices,
            $this->feeRepo
        );
    }

    public function testGetCommandData(): void
    {
        $this->setupFee(true);
        $this->assertSame(
            [
                'id' => $this->item->getEntityId()
            ],
            $this->sut->getCommandData($this->item)
        );
    }

    public function testProcessMessageCanRefund(): void
    {
        $this->setupFee(true);

        $this->expectCommand(
            RefundFee::class,
            [
                'id' => $this->item->getEntityId(),
                'customerReference' => null,
                'customerName' => null,
                'address' => null
            ],
            new Result()
        );

        $this->expectCommand(
            UpdateFeeStatus::class,
            [
                'id' => $this->item->getEntityId(),
                'status' => FeeEntity::STATUS_REFUNDED
            ],
            new Result(),
            false
        );

        $this->expectCommand(
            Complete::class,
            ['item' => $this->item],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($this->item);

        $this->assertEquals(
            'Successfully processed message: ' . $this->item->getId() . ' ',
            $result
        );
    }

    public function testProcessMessageCanNotRefund(): void
    {
        $this->setupFee(false);

        $this->expectCommand(
            Failed::class,
            [
                'item' => $this->item,
                'lastError' => 'Fee cannot be refunded',
            ],
            new Result(),
            false
        );

        $this->expectCommand(
            UpdateFeeStatus::class,
            [
                'id' => $this->item->getEntityId(),
                'status' => FeeEntity::STATUS_REFUND_FAILED
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($this->item);

        $this->assertEquals(
            'Failed to process message: ' . $this->item->getId() . '  Fee cannot be refunded',
            $result
        );
    }

    public function testProcessMessageException(): void
    {
        $this->setupFee(true);

        $this->expectCommandException(
            RefundFee::class,
            [
                'id' => $this->item->getEntityId(),
                'customerReference' => null,
                'customerName' => null,
                'address' => null
            ],
            ORMException::class
        );

        $this->expectCommand(
            Failed::class,
            [
                'item' => $this->item,
                'lastError' => '',
            ],
            new Result(),
            false
        );

        $this->expectCommand(
            UpdateFeeStatus::class,
            [
                'id' => $this->item->getEntityId(),
                'status' => FeeEntity::STATUS_REFUND_FAILED
            ],
            new Result(),
            false
        );

        $this->sut->processMessage($this->item);
    }

    public function testProcessMessageFailed(): void
    {
        $this->setupFee(true);

        $this->expectCommandException(
            RefundFee::class,
            [
                'id' => $this->item->getEntityId(),
                'customerReference' => null,
                'customerName' => null,
                'address' => null
            ],
            DomainException::class
        );

        $this->expectCommand(
            Failed::class,
            [
                'item' => $this->item,
                'lastError' => '',
            ],
            new Result(),
            false
        );

        $this->expectCommand(
            UpdateFeeStatus::class,
            [
                'id' => $this->item->getEntityId(),
                'status' => FeeEntity::STATUS_REFUND_FAILED
            ],
            new Result(),
            false
        );

        $this->sut->processMessage($this->item);
    }

    protected function setupFee(bool $canRefund): void
    {
        $this->fee->shouldReceive('canRefund')
            ->withNoArgs()
            ->andReturn($canRefund);
    }
}
