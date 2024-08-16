<?php

/**
 * Approve Waive
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Approve Waive
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ApproveWaive extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $now = new DateTime();

        /** @var FeeEntity $fee */
        $fee = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $fee->setFeeStatus($this->getRepo()->getRefdataReference(FeeEntity::STATUS_PAID));

        $transaction = $fee->getOutstandingWaiveTransaction();

        if (!$transaction) {
            throw new ValidationException(['pending waive transaction not found']);
        }

        $transaction
            ->setStatus($this->getRepo()->getRefdataReference(TransactionEntity::STATUS_COMPLETE))
            ->setComment($command->getWaiveReason())
            ->setCompletedDate($now)
            ->setProcessedByUser($this->getCurrentUser());

        $this->getRepo()->save($fee);

        $result
            ->addId('fee', $fee->getId())
            ->addMessage('Fee updated')
            ->addId('transaction', $transaction->getId())
            ->addMessage('Waive transaction updated');

        $result->merge(
            $this->handleSideEffect(PayFeeCmd::create(['id' => $fee->getId()]))
        );

        return $result;
    }
}
