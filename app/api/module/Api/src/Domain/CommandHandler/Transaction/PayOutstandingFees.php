<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateOverpaymentFee as CreateOverpaymentFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment as ResolvePaymentCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\RestResponseException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Service\CpmsResponseException;
use Dvsa\Olcs\Api\Service\Exception as ServiceException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Transfer\Command\Fee\RejectWaive as RejectWaiveCmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Pay Outstanding Fees
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class PayOutstandingFees extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface,
    CpmsAwareInterface,
    ConfigAwareInterface
{
    use AuthAwareTrait;
    use CpmsAwareTrait;
    use ConfigAwareTrait;

    public const string ERR_WAIT = 'ERR_WAIT';

    public const string ERR_NO_FEES = 'ERR_NO_FEES';

    public const int DEFAULT_PENDING_PAYMENTS_TIMEOUT = 3600;

    /**
     * @var \Dvsa\Olcs\Api\Service\FeesHelperService
     */
    protected $feesHelper;

    protected $repoServiceName = 'Transaction';

    protected $extraRepos = ['Fee', 'FeeType', 'SystemParameter', 'Task'];

    /**
     * There are three valid use cases for this command
     *  - organisationId AND feeIds
     *  - applicationId only
     *  - feeIds only
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees $command */
        $config = $this->getConfig();
        $pendingPaymentsTimeout = $config['cpms']['pending_payments_timeout'] ?? self::DEFAULT_PENDING_PAYMENTS_TIMEOUT;

        // if payment method in CARD_ONLINE (ie it came from external) and disable card payments is set
        if (
            $command->getPaymentMethod() === FeeEntity::METHOD_CARD_ONLINE &&
            $this->getRepo('SystemParameter')->getDisableSelfServeCardPayments()
        ) {
            $this->result->addMessage('Card payments are disabled');
            return $this->result;
        }

        $extraParams = $this->prepareExtraParams($command);

        if (!empty($command->getOrganisationId())) {
            $fees = $this->getOutstandingFeesForOrganisation($command);
        } elseif (!empty($command->getApplicationId())) {
            $fees = $this->feesHelper->getOutstandingFeesForApplication($command->getApplicationId());
        } elseif (!empty($command->getIrhpApplication())) {
            $fees = $this->feesHelper->getOutstandingFeesForIrhpApplication($command->getIrhpApplication());
        } else {
            $fees = $this->getRepo('Fee')->fetchOutstandingFeesByIds($command->getFeeIds());
        }

        // filter out fees that may have been paid by resolving outstanding payments
        $feesToPay = $this->resolvePaidFees($fees);

        // reload all not paid fees to update transaction statuses after resolving
        if (count($feesToPay) !== 0) {
            $feesToPay = $this->reloadFees($feesToPay);
        }

        if (empty($feesToPay)) {
            $this->result->addMessage(
                [self::ERR_NO_FEES => 'The fee(s) has already been paid']
            );
            return $this->result;
        }

        if ($this->feesHasOutstandingTransactions($feesToPay, $pendingPaymentsTimeout)) {
            $this->result->addMessage(
                [
                    self::ERR_WAIT =>
                        'Error attempting to resolve a previous payment. Please wait 15 minutes and try again'
                ]
            );
            return $this->result;
        }

        if ($command->getShouldResovleOnly() === true) {
            return $this->result;
        }

        try {
            $cardMethods = [FeeEntity::METHOD_CARD_ONLINE, FeeEntity:: METHOD_CARD_OFFLINE];
            if (in_array($command->getPaymentMethod(), $cardMethods)) {
                return $this->cardPayment($command, $feesToPay, $extraParams);
            } else {
                return $this->immediatePayment($command, $feesToPay, $extraParams);
            }
        } catch (CpmsResponseException $e) {
            // rethrow as Domain exception
            throw new RestResponseException(
                sprintf('Error from CPMS service [%s] %s', $e->getMessage(), json_encode($e->getResponse())),
                \Laminas\Http\Response::STATUS_CODE_500,
                $e
            );
        }
    }

    /**
     * If any of fees has outstanding transaction
     *
     * @param array $fees                   fees
     * @param int   $pendingPaymentsTimeout pending payments timeout
     *
     * @return bool
     */
    protected function feesHasOutstandingTransactions($fees, $pendingPaymentsTimeout)
    {
        /** @var FeeEntity $fee */
        foreach ($fees as $fee) {
            if ($fee->hasOutstandingPaymentExcludeWaive($pendingPaymentsTimeout)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reload fees
     *
     * @param array $fees fees
     *
     * @return array
     */
    protected function reloadFees($fees)
    {
        $ids = [];
        foreach ($fees as $fee) {
            $ids[] = $fee->getId();
        }
        return $this->getRepo('Fee')->fetchFeesByIds($ids);
    }

    /**
     * Initiates a CPMS card payment which is a two-step process
     *
     * @param CommandInterface $command     command
     * @param array            $feesToPay   fees to pay
     * @param array            $extraParams extra params
     *
     * @return Result
     */
    protected function cardPayment($command, $feesToPay, $extraParams = [])
    {
        // fire off to CPMS
        if ($command->getPaymentMethod() === FeeEntity::METHOD_CARD_OFFLINE) {
            $response = $this->getCpmsService()->initiateCnpRequest(
                $command->getCpmsRedirectUrl(),
                $feesToPay,
                $extraParams
            );
        } else {
            $response = $this->getCpmsService()->initiateCardRequest(
                $command->getCpmsRedirectUrl(),
                $feesToPay,
                $extraParams
            );
        }

        // create transaction
        $transaction = new TransactionEntity();
        $transaction
            ->setCpmsSchema($response['schema_id'])
            ->setReference($response['receipt_reference'])
            ->setGatewayUrl($response['gateway_url'])
            ->setStatus($this->getRepo()->getRefdataReference(TransactionEntity::STATUS_OUTSTANDING))
            ->setType($this->getRepo()->getRefdataReference(TransactionEntity::TYPE_PAYMENT))
            ->setPaymentMethod($this->getRepo()->getRefdataReference($command->getPaymentMethod()));

        // create feeTransaction record(s) and cancel any pending waives
        $feeTransactions = new ArrayCollection();
        $transaction->setFeeTransactions($feeTransactions);
        foreach ($feesToPay as $fee) {
            $this->result->merge($this->maybeCancelPendingWaive($fee));

            $feeTransaction = new FeeTransactionEntity();
            $feeTransaction
                ->setFee($fee)
                ->setAmount($fee->getOutstandingAmount())
                ->setTransaction($transaction); // needed for cascade persist to work
            $feeTransactions->add($feeTransaction);
        }

        // persist
        $this->getRepo()->save($transaction);

        $this->result->addId('transaction', $transaction->getId());
        $this->result->addMessage('Transaction record created');

        return $this->result;
    }

    /**
     * Cash/cheque/PO payment
     *
     * @param CommandInterface $command     command
     * @param array            $fees        fees
     * @param array            $extraParams extra params
     *
     * @return Result
     */
    protected function immediatePayment($command, $fees, $extraParams = [])
    {
        $this->validateAmount($command->getReceived(), $fees);

        // work out the allocation of the payment amount to fees, will create
        // balancing entry to handle any overpayment
        $allocations = $this->allocatePayments($command->getReceived(), $fees);

        // fire off to relevant CPMS method to record payment
        $response = $this->recordPaymentInCpms($command, $fees, $extraParams);

        $receiptDate = new \DateTime($command->getReceiptDate());
        $chequeDate = $command->getChequeDate() ? new \DateTime($command->getChequeDate()) : null;
        $chequePoNumber = $command->getChequeNo() ?: $command->getPoNo();

        // create transaction
        $transaction = new TransactionEntity();
        $transaction
            ->setCpmsSchema($response['schema_id'])
            ->setReference($response['receipt_reference'])
            ->setStatus($this->getRepo()->getRefdataReference(TransactionEntity::STATUS_PAID))
            ->setType($this->getRepo()->getRefdataReference(TransactionEntity::TYPE_PAYMENT))
            ->setPaymentMethod($this->getRepo()->getRefdataReference($command->getPaymentMethod()))
            ->setCompletedDate($receiptDate)
            ->setPayerName($command->getPayer())
            ->setPayingInSlipNumber($command->getSlipNo())
            ->setProcessedByUser($this->getCurrentUser())
            ->setChequePoDate($chequeDate) // note we don't actually capture date for PO's
            ->setChequePoNumber($chequePoNumber);

        // create feeTransaction record(s) and cancel any pending waives
        foreach ($fees as $fee) {
            $this->result->merge($this->maybeCancelPendingWaive($fee));

            $allocatedAmount = $allocations[$fee->getId()];
            $markAsPaid = ($allocatedAmount === $fee->getOutstandingAmount());
            $feeTransaction = new FeeTransactionEntity();
            $feeTransaction
                ->setFee($fee)
                ->setAmount($allocatedAmount)
                ->setTransaction($transaction); // needed for cascade persist to work
            $transaction->getFeeTransactions()->add($feeTransaction);

            if ($markAsPaid) {
                $fee->setFeeStatus($this->getRepo()->getRefdataReference(FeeEntity::STATUS_PAID));
                $method = $this->getRepo()->getRefdataReference($command->getPaymentMethod())->getDescription();
                $this->result->addMessage('Fee ID ' . $fee->getId() . ' updated as paid by ' . $method);
                // We need to call save() on the fee, it won't cascade persist from the transaction
                $this->getRepo('Fee')->save($fee);
                $this->result->merge($this->handleSideEffect(PayFeeCmd::create(['id' => $fee->getId()])));
            } else {
                if ($fee->getLicence()) {
                    // Generate Insufficient Fee Request letter
                    $this->result->merge($this->generateInsufficientFeeRequestLetter($fee, $allocatedAmount));
                }
                $task = $fee->getTask();
                if ($fee->getTask() === null || $task->getIsClosed() === 'Y') {
                    $this->createTaskForOutstandingFee($fee);
                }
            }
        }

        // persist transaction
        $this->getRepo()->save($transaction);

        $this->result
            ->addId('transaction', $transaction->getId())
            ->addMessage('Transaction record created: ' . $transaction->getReference())
            ->addId('feeTransaction', $transaction->getFeeTransactionIds())
            ->addMessage('FeeTransaction record(s) created');

        return $this->result;
    }

    /**
     * Create task for outstanding fee
     *
     * @param FeeEntity $fee fee
     *
     * @return void
     */
    protected function createTaskForOutstandingFee($fee)
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
        if ($fee->getApplication() !== null) {
            $data['application'] = $fee->getApplication()->getId();
        }
        if ($fee->getLicence() !== null) {
            $data['licence'] = $fee->getLicence()->getId();
        }
        if ($fee->getBusReg() !== null) {
            $data['busReg'] = $fee->getBusReg()->getId();
        }
        if ($fee->getIrfoGvPermit() !== null) {
            $data['irfoOrganisation'] = $fee->getIrfoGvPermit()->getOrganisation()->getId();
        } elseif ($fee->getIrfoPsvAuth() !== null) {
            $data['irfoOrganisation'] = $fee->getIrfoPsvAuth()->getOrganisation()->getId();
        }

        $this->result->merge($this->handleSideEffect(CreateTask::create($data)));
        $fee->setTask(
            $this->getRepo('Task')->fetchById($this->result->getId('task'))
        );

        $this->getRepo('Fee')->save($fee);
    }

    /**
     * Record payment in CPMS
     *
     * @param \Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees $command     command
     * @param array                                                      $fees        fees
     * @param array                                                      $extraParams extra params
     *
     * @return array|false
     * @throws BadRequestException if paymentMethod is invalid
     */
    protected function recordPaymentInCpms($command, $fees, $extraParams = [])
    {
        $response = match ($command->getPaymentMethod()) {
            FeeEntity::METHOD_CASH => $this->getCpmsService()->recordCashPayment(
                $fees,
                $command->getReceived(),
                $command->getReceiptDate(),
                $command->getSlipNo(),
                $extraParams
            ),
            FeeEntity::METHOD_CHEQUE => $this->getCpmsService()->recordChequePayment(
                $fees,
                $command->getReceived(),
                $command->getReceiptDate(),
                $command->getPayer(),
                $command->getSlipNo(),
                $command->getChequeNo(),
                $command->getChequeDate(),
                $extraParams
            ),
            FeeEntity::METHOD_POSTAL_ORDER => $this->getCpmsService()->recordPostalOrderPayment(
                $fees,
                $command->getReceived(),
                $command->getReceiptDate(),
                $command->getSlipNo(),
                $command->getPoNo(),
                $extraParams
            ),
            default => throw new BadRequestException('invalid payment method: ' . $command->getPaymentMethod()),
        };

        return $response;
    }

    /**
     * Resolve outstanding payments
     *
     * @param FeeEntity $fee fee
     *
     * @return boolean whether fee was paid
     */
    protected function resolveOutstandingPayments($fee)
    {
        $paid = true;

        foreach ($fee->getFeeTransactions() as $ft) {
            if ($ft->getTransaction()->isOutstanding()) {
                $transactionId = $ft->getTransaction()->getId();

                // resolve outstanding payment
                $dto = ResolvePaymentCommand::create(
                    [
                        'id' => $transactionId,
                        'paymentMethod' => $ft->getTransaction()->getPaymentMethod()->getId(),
                    ]
                );
                $this->result->merge($this->handleSideEffect($dto));

                // check payment status
                $transaction = $this->getRepo()->fetchById($transactionId);
                if (!$transaction->isPaid()) {
                    $paid = false;
                }
            }
        }

        return $paid;
    }

    /**
     * Resolve paid fees
     *
     * @param array $fees fees
     *
     * @return array
     */
    public function resolvePaidFees($fees)
    {
        $feesToPay = [];
        foreach ($fees as $fee) {
            if ($fee->hasOutstandingPayment()) {
                $paid = $this->resolveOutstandingPayments($fee);
                if (!$paid) {
                    $feesToPay[] = $fee;
                }
            } else {
                // need to reload fee everytime, because transaction status for the current fee
                // can be changed during another fee processing
                $feeToTest = $this->getRepo('Fee')->fetchById($fee->getId());
                if ($feeToTest->getFeeStatus()->getId() !== FeeEntity::STATUS_PAID) {
                    $feesToPay[] = $feeToTest;
                }
            }
        }
        return $feesToPay;
    }

    /**
     * Partial payments are supported for cash/cheque/PO payments but amount
     * must not result in a zero allocation to any fee.
     * The form validation will normally catch any mismatch but it relies on a
     * hidden field so we have a secondary check here in the service layer.
     *
     * @param string $amount amount
     * @param array  $fees   fees
     *
     * @return null
     * @throws ValidationException
     */
    protected function validateAmount($amount, $fees)
    {
        $minAmount = $this->feesHelper->getMinPaymentForFees($fees);
        if ($amount < $minAmount) {
            throw new ValidationException([sprintf("Amount must be at least %1\$.2f", $minAmount)]);
        }
    }

    /**
     * Get outstanding fees for organisation
     *
     * @param CommandInterface $command command
     *
     * @return array
     * @throws RuntimeException
     */
    protected function getOutstandingFeesForOrganisation(CommandInterface $command)
    {
        // get outstanding fees for organisation
        $outstandingFees = $this->getRepo('Fee')
                ->fetchOutstandingFeesByOrganisationId($command->getOrganisationId());

        // filter requested fee ids against outstanding fees
        $fees = [];
        if (!empty($outstandingFees)) {
            $ids = $command->getFeeIds();
            foreach ($outstandingFees as $fee) {
                if (in_array($fee->getId(), $ids)) {
                    $fees[] = $fee;
                }
            }
        }
        return $fees;
    }

    /**
     * Allocate payments
     *
     * @param string $receivedAmount received amount
     * @param array  $fees           fees passed by reference as we may need to append
     *
     * @return array
     */
    protected function allocatePayments($receivedAmount, &$fees)
    {
        $dtoData = [
            'receivedAmount' => $receivedAmount,
            'fees' => $fees,
        ];

        $feeResult = $this->handleSideEffect(CreateOverpaymentFeeCmd::create($dtoData));

        if ($feeResult->getId('fee')) {
            // an overpayment balancing fee was created, add it to the list
            $fees[] = $this->getRepo('Fee')->fetchById($feeResult->getId('fee'));
        }

        $this->result->merge($feeResult);

        try {
            // work out the allocation of the payment amount to fees
            $allocations = $this->feesHelper->allocatePayments($receivedAmount, $fees);
        } catch (ServiceException $e) {
            // if there is an allocation error, rethrow as Domain exception
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $allocations;
    }

    /**
     * Generate insufficient fee request letter
     *
     * @param FeeEntity $fee             fee
     * @param string    $allocatedAmount allocated amount
     *
     * @return Result
     */
    private function generateInsufficientFeeRequestLetter(FeeEntity $fee, $allocatedAmount)
    {
        // we need to calculate actual outstanding fee, because new transaction is not saved
        // on this step, so $fee->getOutstandingFeeAmount() will return the previous value
        $actualOutstandingAmount = $fee->getOutstandingAmount() - $allocatedAmount;
        $receivedAmount = $fee->getGrossAmount() - $actualOutstandingAmount;

        $dtoData = [
            'template' => 'FEE_REQ_INSUFFICIENT',
            'query' => [
                'fee' => $fee->getId(),
                'licence' => $fee->getLicence()->getId()
            ],
            'knownValues' => [
                'INSUFFICIENT_FEE_TABLE' => [
                    'receivedAmount' => $receivedAmount,
                    'outstandingAmount' => $actualOutstandingAmount
                ]
            ],
            'description' => 'Insufficient Fee Request',
            'licence'     => $fee->getLicence()->getId(),
            'category'    => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_FEE_REQUEST,
            'isExternal'  => false,
            'dispatch'    => true
        ];
        if ($fee->getApplication()) {
            $dtoData['application'] = $fee->getApplication()->getId();
        }

        return $this->handleSideEffect(GenerateAndStore::create($dtoData));
    }

    /**
     * If there is an outstanding waive transaction for a fee, reject it
     *
     * @param FeeEntity $fee fee
     *
     * @return Result
     */
    private function maybeCancelPendingWaive(FeeEntity $fee)
    {
        $result = new Result();

        if ($fee->getOutstandingWaiveTransaction()) {
            $rejectCmd = RejectWaiveCmd::create(['id' => $fee->getId()]);
            $result->merge($this->handleSideEffect($rejectCmd));
        }

        return $result;
    }

    /**
     * Prepare extra params
     *
     * @param \Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees $command command
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
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->feesHelper = $container->get('FeesHelperService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
