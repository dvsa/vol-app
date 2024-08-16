<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * Trait RefundInterimTrait
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Traits
 */
trait RefundInterimTrait
{
    protected function maybeRefundInterimFee(Application $application): void
    {
        /** @var Fee $fee */
        foreach ($application->getFees() as $fee) {
            if ($fee->canRefund() && $fee->getFeeType()->isInterimGrantFee()) {
                $createCommand = Create::create(
                    [
                        'entityId' => $fee->getId(),
                        'type' => Queue::TYPE_REFUND_INTERIM_FEES,
                        'status' => Queue::STATUS_QUEUED,
                    ]
                );
                $this->result->merge($this->handleSideEffect($createCommand));

                $updateCommand = UpdateFeeStatus::create(
                    [
                        'id' => $fee->getId(),
                        'status' => Fee::STATUS_REFUND_PENDING
                    ]
                );
                $this->result->merge($this->handleSideEffect($updateCommand));
            }
        }
    }
}
