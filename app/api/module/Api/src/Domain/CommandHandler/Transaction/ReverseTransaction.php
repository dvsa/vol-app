<?php

/**
 * Reverse Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Fee\ResetFees as ResetFeesCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Service\CpmsResponseException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Reverse Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ReverseTransaction extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface,
    CpmsAwareInterface
{
    use AuthAwareTrait;
    use CpmsAwareTrait;

    protected $repoServiceName = 'Transaction';

    protected $extraRepos = ['Fee'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $originalTransaction = $this->getRepo()->fetchUsingId($command);

        $this->validate($originalTransaction);
        $extraParams = $this->prepareExtraParams($command);

        try {
            $fee = $originalTransaction->getFeeTransactions()->first()->getFee();
            $response = $this->getCpmsService()->reversePayment(
                $originalTransaction->getReference(),
                $originalTransaction->getPaymentMethod()->getId(),
                [$fee],
                $extraParams
            );
        } catch (CpmsResponseException $e) {
            // rethrow as Domain exception
            throw new RuntimeException(
                'Error from CPMS service: ' . json_encode($e->getResponse()),
                $e->getCode(),
                $e
            );
        }

        // create reversal transaction
        $transactionReference = $response['receipt_reference'];
        $comment = $command->getReason();
        $now = new DateTime();
        $newTransaction = new TransactionEntity();
        $newTransaction
            ->setType($this->getRepo()->getRefdataReference(TransactionEntity::TYPE_REVERSAL))
            ->setStatus($this->getRepo()->getRefdataReference(TransactionEntity::STATUS_COMPLETE))
            ->setComment($comment)
            ->setCompletedDate($now)
            ->setProcessedByUser($this->getCurrentUser())
            ->setReference($transactionReference);

        // copy some details from original transaction (OLCS-11417)
        $this->copyTransactionDetails($originalTransaction, $newTransaction);

        $fees = [];
        foreach ($originalTransaction->getFeeTransactionsForReversal() as $originalFt) {
            $feeTransaction = new FeeTransactionEntity();
            $reversalAmount = $originalFt->getAmount() * -1;
            $fee = $originalFt->getFee();
            $feeTransaction
                ->setFee($fee)
                ->setTransaction($newTransaction)
                ->setAmount($reversalAmount)
                ->setReversedFeeTransaction($originalFt);
            $newTransaction->getFeeTransactions()->add($feeTransaction);
            $fees[$fee->getId()] = $fee;
        }

        $this->getRepo()->save($newTransaction);

        $this->result
            ->addMessage(
                sprintf('Transaction %d reversed', $originalTransaction->getId())
            )
            ->addId('transaction', $newTransaction->getId())
            ->addMessage('Transaction record created');

        $this->result->merge(
            $this->handleSideEffect(ResetFeesCmd::create(['fees' => $fees]))
        );

        return $this->result;
    }

    /**
     * Copy payment method, payer name, cheque/po no., cheque/po date & paying
     * in slip no. from one transaction to another
     *
     * @param TransactionEntity $original original transaction
     * @param TransactionEntity $new      new transaction
     *
     * @return null
     */
    private function copyTransactionDetails(TransactionEntity $original, TransactionEntity &$new)
    {
        $new->setPaymentMethod($original->getPaymentMethod());
        $new->setPayerName($original->getPayerName());
        $new->setChequePoDate($original->getChequePoDate());
        $new->setChequePoNumber($original->getChequePoNumber());
        $new->setPayingInSlipNumber($original->getPayingInSlipNumber());
    }

    /**
     * Validate
     *
     * @param TransactionEntity $transaction transaction entity
     *
     * @return bool
     * @throws ValidationException
     */
    public function validate(TransactionEntity $transaction)
    {
        if (!$transaction->isComplete()) {
            throw new ValidationException(['Cannot reverse a pending transaction']);
        }

        if (!$transaction->canReverse()) {
            throw new ValidationException(['Cannot reverse this transaction']);
        }

        return true;
    }

    /**
     * Prepare extra params
     *
     * @param \Dvsa\Olcs\Transfer\Command\Transaction\ReverseTransaction $command command
     *
     * @return array
     */
    private function prepareExtraParams($command)
    {
        $extraParams = [];

        if ($command->getCustomerName()) {
            $extraParams['customer_name'] = $command->getCustomerName();
        }

        if ($command->getCustomerReference()) {
            $extraParams['customer_reference'] = $command->getCustomerReference();
        }

        if ($command->getAddress()) {
            $extraParams['customer_address'] = $command->getAddress();
        }

        return $extraParams;
    }
}
