<?php

/**
 * Complete Payment
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Transaction;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment as ResolvePaymentCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as SubmitApplicationCmd;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptIrhpPermits;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication as SubmitIrhpApplicationCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompleteTransactionCmd;

/**
 * Complete Payment
 * (completes a CPMS payment)
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class CompleteTransaction extends AbstractCommandHandler implements TransactionedInterface, CpmsAwareInterface
{
    use CpmsAwareTrait;

    protected $repoServiceName = 'Transaction';

    /**
     * Handle command
     *
     * @param CommandInterface $command complete transaction command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CompleteTransactionCmd $command
         * @var Transaction            $transaction
         */

        // ensure payment ref exists
        $reference = $command->getReference();

        // check payment status
        $transaction = $this->getRepo()->fetchbyReference($reference);
        if (!$transaction->isOutstanding()) {
            throw new ValidationException(
                ['Invalid transaction status: ' . $transaction->getStatus()->getId()]
            );
        }
        $fees = $transaction->getFees();

        // update CPMS
        $this->getCpmsService()->handleResponse($reference, $command->getCpmsData(), reset($fees));

        // resolve payment
        $this->result->merge($this->resolvePayment($command, $transaction));

        // handle application submission
        if ($transaction->isPaid() && $command->getSubmitApplicationId()) {
            $this->result->merge($this->updateApplication($command));
        }

        if ($transaction->isPaid()) {
            /** @var Fee $fee */
            foreach ($fees as $fee) {
                if (!empty($fee->getIrhpApplication())) {
                    $this->updateIrhpApplication($fee);
                }
            }
        }

        $this->result->addId('transaction', $transaction->getId());
        $this->result->addMessage('CPMS record updated');
        return $this->result;
    }

    protected function resolvePayment($command, $transaction)
    {
        return $this->handleSideEffect(
            ResolvePaymentCommand::create(
                [
                    'id' => $transaction->getId(),
                    'paymentMethod' => $command->getPaymentMethod(),
                ]
            )
        );
    }

    /**
     * @param $command
     * @return Result
     */
    protected function updateApplication($command)
    {
        return $this->handleSideEffect(
            SubmitApplicationCmd::create(
                [
                    'id' => $command->getSubmitApplicationId(),
                    // we don't have the application version when we call
                    // this as an internal command - we would have to store
                    // it at the point we initiate the payment flow
                ]
            )
        );
    }

    /**
     * Submit the Irhp application
     *
     * @param Fee $fee Fee object
     *
     * @return void
     */
    protected function updateIrhpApplication(Fee $fee)
    {
        $irhpApplication = $fee->getIrhpApplication();

        if ($irhpApplication->canBeSubmitted()) {
            $this->result->merge(
                $this->handleSideEffect(
                    SubmitIrhpApplicationCmd::create(
                        ['id' => $irhpApplication->getId()]
                    )
                )
            );
        } elseif ($irhpApplication->isAwaitingFee()) {
            $this->result->merge(
                $this->handleSideEffect(
                    AcceptIrhpPermits::create(
                        ['id' => $irhpApplication->getId()]
                    )
                )
            );
        }
    }
}
