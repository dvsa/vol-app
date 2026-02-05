<?php

/**
 * Update IRFO Permit Stock Issued
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as IrfoPermitStockEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update IRFO Permit Stock Issued
 */
final class UpdateIrfoPermitStockIssued extends AbstractCommandHandler implements TransactionedInterface
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

        $status = $this->getRepo()->getRefdataReference(IrfoPermitStockEntity::STATUS_ISSUED);

        $irfoGvPermit = $this->getRepo()->getReference(
            IrfoGvPermitEntity::class,
            $command->getIrfoGvPermit()
        );

        $irfoPermitStockList = $this->getRepo()->fetchByIds($ids);

        foreach ($irfoPermitStockList as $irfoPermitStock) {
            // set status
            $irfoPermitStock->setStatus($status);

            // link with IRFO GV Permit
            $irfoPermitStock->setIrfoGvPermit($irfoGvPermit);

            $this->getRepo()->save($irfoPermitStock);
        }

        $result = new Result();
        $result->addMessage('IRFO Permit Stock updated successfully');

        return $result;
    }
}
