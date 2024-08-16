<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Transaction;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as Cpms;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Task\Task;

/**
 * Resolve Payment
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ResolvePayment extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface,
    CpmsAwareInterface
{
    use AuthAwareTrait;
    use CpmsAwareTrait;

    protected $repoServiceName = 'Transaction';

    protected $extraRepos = ['Fee', 'Task'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $transaction Transaction */
        $transaction = $this->getRepo()->fetchUsingId($command);
        $result->addId('transaction', $transaction->getId());

        $finalStatuses = [
            Transaction::STATUS_PAID,
            Transaction::STATUS_CANCELLED,
            Transaction::STATUS_FAILED
        ];
        if (in_array($transaction->getStatus()->getId(), $finalStatuses)) {
            $result->addMessage('Transaction is already resolved');
            return $result;
        }

        if ($transaction->isWaive()) {
            $result->addMessage(sprintf('Waive transaction %d not resolved', $transaction->getId()));
            return $result;
        }

        $fees = $transaction->getFees();
        $statusDetails = $this->getCpmsService()->getPaymentStatus(
            $transaction->getReference(),
            reset($fees)
        );
        $cpmsStatus = $statusDetails['code'];

        $now = new DateTime();

        $transaction
            ->setCompletedDate($now)
            ->setProcessedByUser($this->getCurrentUser());

        switch ($cpmsStatus) {
            case Cpms::PAYMENT_SUCCESS:
                $transaction->setStatus($this->getRepo()->getRefdataReference(Transaction::STATUS_PAID));
                $result->merge($this->updateFees($transaction));
                break;
            case Cpms::PAYMENT_CANCELLATION:
                $transaction->setStatus($this->getRepo()->getRefdataReference(Transaction::STATUS_CANCELLED));
                break;
            case Cpms::PAYMENT_FAILURE:
            case Cpms::PAYMENT_GATEWAY_ERROR:
            case Cpms::PAYMENT_SYSTEM_ERROR:
            case Cpms::PAYMENT_ABANDONED:
                // resolve any abandoned payments as 'failed'
                $transaction->setStatus($this->getRepo()->getRefdataReference(Transaction::STATUS_FAILED));
                break;
            case Cpms::PAYMENT_IN_PROGRESS:
            case Cpms::PAYMENT_AWAITING_GATEWAY_URL:
            case Cpms::PAYMENT_GATEWAY_REDIRECT_URL_RECEIVED:
            case Cpms::PAYMENT_END_OF_FLOW_SIGNALLED:
            case Cpms::PAYMENT_CARD_PAYMENT_CONFIRMED:
            case Cpms::PAYMENT_ACTIVELY_BEING_TAKEN:
                // do nothing, wait for CPMS to update status
                $result->addMessage(
                    sprintf('Transaction %d is pending, CPMS status is %s', $transaction->getId(), $cpmsStatus)
                );
                return $result;
            default:
                // output something to the console log and continue
                $result->addMessage(
                    sprintf(
                        'Unexpected status received from CPMS, transaction %d status %s, message: %s',
                        $transaction->getId(),
                        $cpmsStatus,
                        $statusDetails['message']
                    )
                );
                return $result;
        }

        $this->getRepo()->save($transaction);

        $result->addMessage(
            sprintf(
                'Transaction %d resolved as %s',
                $transaction->getId(),
                $transaction->getStatus()->getDescription()
            )
        );

        return $result;
    }

    /**
     * Update fees that may now have been paid in full by a completed transaction
     *
     * @param Transaction
     * @return Result
     */
    protected function updateFees($transaction)
    {
        $result = new Result();

        $paidStatusRef = $this->getRepo()->getRefdataReference(Fee::STATUS_PAID);

        /** @var FeeTransaction $ft */
        foreach ($transaction->getFeeTransactions() as $ft) {
            /** @var Fee $fee */
            $fee = $ft->getFee();
            $outstanding = Fee::amountToPence($fee->getOutstandingAmount()); // convert to integer pence for comparison
            if ($outstanding <= 0) {
                $fee->setFeeStatus($paidStatusRef);
                $this->getRepo('Fee')->save($fee);
                $result->addMessage('Fee ID ' . $fee->getId() . ' updated as paid');
                // trigger side effects
                $result->merge(
                    $this->handleSideEffect(PayFeeCmd::create(['id' => $fee->getId()]))
                );
                $createTaskResult = $this->checkForOrphanedPaymentsAndCreateTask($fee);
                if ($createTaskResult !== null) {
                    $result->merge($createTaskResult);
                }
            } elseif ($fee->getTask() === null) {
                $result->merge($this->handleSideEffect($this->createCreateTaskCommand()));
                $fee->setTask(
                    $this->getRepo('Task')->fetchById($result->getId('task'))
                );
                $this->getRepo('Fee')->save($fee);
            }
        }

        return $result;
    }

    /**
     * Create command for create task
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask
     */
    protected function createCreateTaskCommand()
    {
        $currentUser = $this->getCurrentUser();
        $data = [
            'category' => Task::CATEGORY_LICENSING,
            'subCategory' => Task::SUBCATEGORY_LICENSING_GENERAL_TASK,
            'description' => Task::TASK_DESCRIPTION_FEE_DUE,
            'actionDate' => (new DateTime())->format(\DateTime::W3C),
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId()
        ];

        return CreateTask::create($data);
    }

    /**
     * Check for orphaned payments and create task if needed
     *
     * @param Fee $fee fee
     *
     * @return null|\Dvsa\Olcs\Api\Domain\Command\Task\CreateTask
     */
    protected function checkForOrphanedPaymentsAndCreateTask(Fee $fee)
    {
        $feeTransactions = $fee->getFeeTransactions();
        if ($feeTransactions->count() === 1) {
            return null;
        }
        $totalAmount = 0;
        /** @var FeeTransaction $feeTransaction */
        foreach ($feeTransactions as $feeTransaction) {
            /** @var Transaction $txn */
            $txn = $feeTransaction->getTransaction();
            if ($txn->getStatus()->getId() === Transaction::STATUS_COMPLETE) {
                $totalAmount += Fee::amountToPence($feeTransaction->getAmount());
            }
        }
        if ($totalAmount > Fee::amountToPence($fee->getGrossAmount())) {
            $feeLicence = $fee->getLicence();
            $feeApplication = $fee->getApplication();
            $feeBusReg = $fee->getBusReg();
            $feeIrfoGvPermit = $fee->getIrfoGvPermit();
            $feeIrfoPsvAuth = $fee->getIrfoPsvAuth();
            $irfoOrganisationId = null;
            if ($feeIrfoGvPermit !== null) {
                $irfoOrganisationId = $feeIrfoGvPermit->getOrganisation()->getId();
            } elseif ($feeIrfoPsvAuth !== null) {
                $irfoOrganisationId = $feeIrfoPsvAuth->getOrganisation()->getId();
            }
            $data = [
                'category' => Task::CATEGORY_LICENSING,
                'subCategory' => Task::SUBCATEGORY_LICENSING_GENERAL_TASK,
                'description' => sprintf(
                    Task::TASK_DESCRIPTION_DUPLICATED,
                    $fee->getFeeType()->getFeeType()->getDescription(),
                    $fee->getId()
                ),
                'actionDate' => (new DateTime('now'))->format(\DateTime::W3C),
                'isClosed' => 0,
                'urgent' => 1,
                'licence' => $feeLicence !== null ? $feeLicence->getId() : null,
                'application' => $feeApplication !== null ? $feeApplication->getId() : null,
                'busReg' => $feeBusReg !== null ? $feeBusReg->getId() : null,
                'irfoOrganisation' => $irfoOrganisationId
            ];

            return $this->handleSideEffect(CreateTask::create($data));
        }
        return null;
    }
}
