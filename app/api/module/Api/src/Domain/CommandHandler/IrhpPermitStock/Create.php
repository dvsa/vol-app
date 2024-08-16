<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitJurisdiction\Create as CreateDevolvedQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitSector\Create as CreateSectorQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as StockEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create an IRHP Permit Stock
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    use IrhpPermitStockTrait;

    protected $repoServiceName = 'IrhpPermitStock';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        // This shared method is defined in IrhpPermitStockTrait - and can throw a ValidationException
        $this->validityPeriodValidation($command);
        $references = $this->resolveReferences($command);

        $stock = StockEntity::create(
            $references['irhpPermitType'],
            $references['country'],
            $references['permitCategory'],
            $command->getInitialStock(),
            $this->getRepo()->getRefDataReference(StockEntity::STATUS_SCORING_NEVER_RUN),
            $references['applicationPathGroup'],
            $this->getRepo()->getRefdataReference($command->getBusinessProcess()),
            $command->getPeriodNameKey(),
            $command->getValidFrom(),
            $command->getValidTo(),
            $command->getHiddenSs()
        );

        $this->getRepo('IrhpPermitStock')->save($stock);

        $stockId = $stock->getId();

        $sideEffects = [
            CreateSectorQuotasCmd::create(['id' => $stockId]),
            CreateDevolvedQuotasCmd::create(['id' => $stockId])
        ];

        $this->result->merge(
            $this->handleSideEffects($sideEffects)
        );

        $this->result->addId('IrhpPermitStock', $stockId);
        $this->result->addMessage("IRHP Permit Stock '{$stockId}' created");

        return $this->result;
    }
}
