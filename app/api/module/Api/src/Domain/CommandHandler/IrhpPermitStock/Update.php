<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as StockEntity;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock;

/**
 * Update an IRHP Permit Stock
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class Update extends AbstractCommandHandler
{
    use IrhpPermitStockTrait;

    protected $repoServiceName = 'IrhpPermitStock';
    protected $extraRepos = ['IrhpPermitType'];

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        // This shared method is defined in IrhpPermitStockTrait - and can throw a ValidationException
        $this->validityPeriodValidation($command);
        $references = $this->resolveReferences($command);

        /**
         * @var IrhpPermitStock $command
         * @var StockEntity $stock
         */
        $stock = $this->getRepo()->fetchUsingId($command);

        $stock->update(
            $references['irhpPermitType'],
            $references['country'],
            $references['permitCategory'],
            $command->getInitialStock(),
            $command->getPeriodNameKey(),
            $command->getValidFrom(),
            $command->getValidTo(),
            $command->getHiddenSs()
        );

        $this->getRepo()->save($stock);

        $this->result->addId('Irhp Permit Stock', $stock->getId());
        $this->result->addMessage("Irhp Permit Stock '{$stock->getId()}' updated");

        return $this->result;
    }
}
