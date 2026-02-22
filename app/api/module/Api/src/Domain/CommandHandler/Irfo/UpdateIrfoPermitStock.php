<?php

/**
 * Update IrfoPermitStock
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as IrfoPermitStockEntity;

/**
 * Update IrfoPermitStock
 */
final class UpdateIrfoPermitStock extends AbstractCommandHandler implements TransactionedInterface
{
    public const int MAX_IDS_COUNT = 100;

    protected $repoServiceName = 'IrfoPermitStock';

    /**
     * Update IRFO Permit Stock Issued
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();

        if (count($ids) > self::MAX_IDS_COUNT) {
            throw new Exception\ValidationException(
                ['Number of selected records must be less than or equal to ' . self::MAX_IDS_COUNT]
            );
        }

        $status = $this->getRepo()->getRefdataReference($command->getStatus());

        // if updating to In Stock - unassign the permit
        $unassignIrfoGvPermit = ($status->getId() === IrfoPermitStockEntity::STATUS_IN_STOCK);

        $irfoPermitStockList = $this->getRepo()->fetchByIds($ids);

        foreach ($irfoPermitStockList as $irfoPermitStock) {
            $irfoPermitStock->setStatus($status);

            if ($unassignIrfoGvPermit) {
                $irfoPermitStock->setIrfoGvPermit(null);
            }

            $this->getRepo()->save($irfoPermitStock);
        }

        $result = new Result();
        $result->addMessage('IRFO Permit Stock updated successfully');

        return $result;
    }
}
