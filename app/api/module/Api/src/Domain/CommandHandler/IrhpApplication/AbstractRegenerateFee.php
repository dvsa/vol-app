<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Abstract create fee (or replace if already present)
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
abstract class AbstractRegenerateFee extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['FeeType'];

    /** @var string The human-readable name of the fee */
    protected $feeName = 'changeMe';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationId = $command->getId();
        $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);

        if (!$this->canCreateOrReplaceFee($irhpApplication)) {
            throw new ForbiddenException(
                'IRHP application is not in the correct state to allow create/replace of ' . $this->feeName
            );
        }

        $feeCommands = [];

        $outstandingFees = $this->getOutstandingFees($irhpApplication);
        foreach ($outstandingFees as $fee) {
            $feeCommands[] = CancelFee::create(['id' => $fee->getId()]);
        }

        $productRefsAndQuantities = $this->getFeeProductRefsAndQuantities($irhpApplication);
        $licenceId = $irhpApplication->getLicence()->getId();
        $invoicedDate = date('Y-m-d');

        foreach ($productRefsAndQuantities as $productRef => $quantity) {
            $feeType = $this->getRepo('FeeType')->getLatestByProductReference($productRef);

            $description = sprintf(
                '%s - %d at £%d',
                $feeType->getDescription(),
                $quantity,
                $feeType->getFixedValue()
            );

            $feeCommands[] = CreateFee::create(
                [
                    'licence' => $licenceId,
                    'irhpApplication' => $irhpApplicationId,
                    'invoicedDate' => $invoicedDate,
                    'description' => $description,
                    'feeType' => $feeType->getId(),
                    'feeStatus' => Fee::STATUS_OUTSTANDING,
                    'quantity' => $quantity,
                ]
            );
        }

        $this->result->merge(
            $this->handleSideEffects($feeCommands)
        );

        $this->result->addMessage('Refreshed ' . $this->feeName . ' list');
        $this->result->addId('irhpApplication', $irhpApplicationId);
        return $this->result;
    }

    /**
     * Whether a fee can be created or replaced
     *
     *
     * @return bool
     */
    abstract protected function canCreateOrReplaceFee(IrhpApplication $irhpApplication);

    /**
     * Get outstanding fees on this application
     *
     *
     * @return array
     */
    abstract protected function getOutstandingFees(IrhpApplication $irhpApplication);

    /**
     * Get an array of key/value pairs representing the product references and quantities of each fee that needs to be
     * created
     *
     *
     * @return array
     */
    abstract protected function getFeeProductRefsAndQuantities(IrhpApplication $irhpApplication);
}
