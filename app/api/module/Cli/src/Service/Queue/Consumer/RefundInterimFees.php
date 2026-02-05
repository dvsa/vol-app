<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Domain\Repository\Fee;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Transfer\Command\Fee\RefundFee;

class RefundInterimFees extends AbstractCommandConsumer
{
    protected $commandName = RefundFee::class;

    /** @var Fee */
    protected $feeRepo;

    /**
     * RefundInterimFees constructor.
     *
     * @param AbstractConsumerServices $abstractConsumerServices
     * @param Fee $feeRepo
     */
    public function __construct(
        AbstractConsumerServices $abstractConsumerServices,
        Fee $feeRepo
    ) {
        parent::__construct($abstractConsumerServices);
        $this->feeRepo = $feeRepo;
    }

    public function getCommandData(QueueEntity $item)
    {
        return ['id' => $item->getEntityId()];
    }

    #[\Override]
    public function processMessage(QueueEntity $item)
    {
        /** @var FeeEntity $fee */
        $fee = $this->feeRepo->fetchById($item->getEntityId());

        // has already been refunded
        if (!$fee->canRefund()) {
            $this->updateRefundStatus($item->getEntityId(), FeeEntity::STATUS_REFUND_FAILED);
            return $this->failed($item, "Fee cannot be refunded");
        }

        try {
            $result = parent::processMessage($item);
        } catch (\Exception $exception) {
            $this->updateRefundStatus($item->getEntityId(), FeeEntity::STATUS_REFUND_FAILED);
            throw $exception;
        }

        if (str_starts_with($result, 'Successfully')) {
            $this->updateRefundStatus($item->getEntityId(), FeeEntity::STATUS_REFUNDED);
        } else {
            $this->updateRefundStatus($item->getEntityId(), FeeEntity::STATUS_REFUND_FAILED);
        }

        return $result;
    }

    protected function updateRefundStatus(int $id, string $status)
    {
        $command = UpdateFeeStatus::create(
            [
                'id' => $id,
                'status' => $status
            ]
        );
        $this->handleSideEffectCommand($command);
    }
}
