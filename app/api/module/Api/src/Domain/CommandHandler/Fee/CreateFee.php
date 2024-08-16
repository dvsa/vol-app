<?php

/**
 * Create Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Fee\CreateFee as Cmd;
use Dvsa\Olcs\Transfer\Command\Fee\RecommendWaive as RecommendWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\ApproveWaive as ApproveWaiveCmd;

/**
 * Create Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateFee extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        $feeType = $this->getRepo()->getReference(FeeType::class, $command->getFeeType());

        $this->validate($command, $feeType);

        $fee = $this->createFeeObject($command);

        $this->getRepo()->save($fee);

        if ($fee->getFeeStatus()->getId() === Fee::STATUS_PAID) {
            // created fee is paid - recommend / approve waive
            $waiveData = [
                'id' => $fee->getId(),
                'version' => $fee->getVersion(),
                'waiveReason' => method_exists($command, 'getWaiveReason') ? $command->getWaiveReason() : null,
            ];

            $this->handleSideEffect(RecommendWaiveCmd::create($waiveData));
            $this->handleSideEffect(ApproveWaiveCmd::create($waiveData));
        }

        $result = new Result();
        $result->addId('fee', $fee->getId());
        $result->addMessage('Fee created successfully');

        return $result;
    }

    /**
     * @return Fee
     */
    private function createFeeObject(Cmd $command)
    {
        $feeType = $this->getRepo()->getReference(FeeType::class, $command->getFeeType());
        $feeStatus = $this->getRepo()->getRefdataReference($command->getFeeStatus());

        $amount = (
            $command->getQuantity() > 1 && $command->getAmount() !== null
                ? $command->getAmount() * $command->getQuantity()
                : $command->getAmount()
        );
        $fee = new Fee($feeType, $amount, $feeStatus);

        if ($command->getInvoicedDate() !== null) {
            $fee->setInvoicedDate(new \DateTime($command->getInvoicedDate()));
        }

        if ($command->getDescription() !== null) {
            $fee->setDescription($command->getDescription());
        } else {
            $fee->setDescription($feeType->getDescription());
        }

        $this->handleTaskLink($command, $fee);
        $this->handleLicenceLink($command, $fee);
        $this->handleIrhpApplicationLink($command, $fee);
        $this->handleIrhpPermitApplicationLink($command, $fee);
        $this->handleIrfoGvPermitLink($command, $fee);
        $this->handleIrfoPsvAuthLink($command, $fee);
        $this->handleApplicationLink($command, $fee);
        $this->handleBusRegLink($command, $fee);

        // if amount is null, we should use the amount from the feeType
        if (is_null($fee->getNetAmount())) {
            $amount = (
                ($command->getQuantity() > 1)
                    ? $feeType->getAmount() * $command->getQuantity()
                    : $feeType->getAmount()
            );
            $fee->setNetAmount($amount);
            $fee->setVatandGrossAmountsFromNetAmountUsingRate($feeType->getVatRate());
        }

        if (method_exists($command, 'getIrfoFeeExempt') && ($command->getIrfoFeeExempt() !== null)) {
            $fee->setIrfoFeeExempt($command->getIrfoFeeExempt());
        }
        // if amount is 0 set the status to paid
        if (empty((float) $fee->getNetAmount())) {
            $fee->setFeeStatus($this->getRepo()->getRefdataReference(Fee::STATUS_PAID));
        }

        return $fee;
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleTaskLink($command, $fee)
    {
        if ($command->getTask() !== null) {
            $fee->setTask($this->getRepo()->getReference(Task::class, $command->getTask()));
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleLicenceLink($command, $fee)
    {
        if ($command->getLicence() !== null) {
            $fee->setLicence($this->getRepo()->getReference(Licence::class, $command->getLicence()));
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleIrhpApplicationLink($command, $fee)
    {
        if ($command->getIrhpApplication() !== null) {
            $fee->setIrhpApplication($this->getRepo()->getReference(IrhpApplication::class, $command->getIrhpApplication()));
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleIrhpPermitApplicationLink($command, $fee)
    {
        if ($command->getIrhpPermitApplication() !== null) {
            $fee->setIrhpPermitApplication(
                $this->getRepo()->getReference(IrhpPermitApplication::class, $command->getIrhpPermitApplication())
            );
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleIrfoGvPermitLink($command, $fee)
    {
        if ($command->getIrfoGvPermit() !== null) {
            $fee->setIrfoGvPermit($this->getRepo()->getReference(IrfoGvPermit::class, $command->getIrfoGvPermit()));
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleIrfoPsvAuthLink($command, $fee)
    {
        if ($command->getIrfoPsvAuth() !== null) {
            $fee->setIrfoPsvAuth($this->getRepo()->getReference(IrfoPsvAuth::class, $command->getIrfoPsvAuth()));
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleApplicationLink($command, $fee)
    {
        if ($command->getApplication() !== null) {
            $application = $this->getRepo()->getReference(Application::class, $command->getApplication());
            $fee->setApplication($application);
            if (!$fee->getLicence()) {
                // if licence id wasn't specified, link the application's licence
                $fee->setLicence($application->getLicence());
            }
        }
    }

    /**
     * @param Cmd $command
     * @param Fee $fee
     * @return null
     */
    private function handleBusRegLink($command, $fee)
    {
        if ($command->getBusReg() !== null) {
            $busReg = $this->getRepo()->getReference(BusReg::class, $command->getBusReg());
            $fee->setBusReg($busReg);
            if (!$fee->getLicence()) {
                // if licence id wasn't specified, link the bus reg's licence
                $fee->setLicence($busReg->getLicence());
            }
        }
    }

    /**
     * @throws ValidationException
     * @return bool
     */
    public function validate(Cmd $command, $feeType)
    {
        if ($feeType->isMiscellaneous()) {
            // misc fees don't need linked entities
            return true;
        }

        if ($feeType->isAdjustment()) {
            // balancing/overpayment fees don't need linked entities
            return true;
        }

        $this->validateLinkedEntity($command);

        if ($command->getIrfoGvPermit() && $command->getIrfoPsvAuth()) {
            $msg = 'Fee must be linked to a either GV Permit or PSV Authorisation but not both';
            throw new ValidationException(
                [
                    'irfoGvPermit' => [$msg],
                    'irfoPsvAuth' => [$msg],
                ]
            );
        }

        return true;
    }

    /**
     * @throws ValidationException
     */
    private function validateLinkedEntity(Cmd $command)
    {
        if (
            empty($command->getLicence())
            && empty($command->getApplication())
            && empty($command->getBusReg())
            && empty($command->getTask())
            && empty($command->getIrfoGvPermit())
            && empty($command->getIrfoPsvAuth())
        ) {
            throw new ValidationException(['Fee must be linked to an entity']);
        }
    }
}
