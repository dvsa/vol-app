<?php

/**
 * Create IrfoPermitStock
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry;

/**
 * Create IrfoPermitStock
 */
final class CreateIrfoPermitStock extends AbstractCommandHandler implements TransactionedInterface
{
    public const int MAX_DIFF = 100;
    public const string ERROR_INVALID_START_END = 'IRFO-PS-1';
    public const string ERROR_MAX_DIFF_EXCEEDED = 'IRFO-PS-2';

    protected $repoServiceName = 'IrfoPermitStock';

    public function handleCommand(CommandInterface $command)
    {
        // IRFO Permit Stock is NOT a standard CRUD
        // It needs to find all existing IrfoPermitStock records where
        // - serialNo is between serialNoStart and serialNoEnd (inclusive)
        // - validForYear is the one selected
        // - irfoCountry is the one selected
        // and update status of the record (if already exists) or create a new record

        // validation of SerialNoStart and SerialNoEnd
        if ((int)$command->getSerialNoStart() > (int)$command->getSerialNoEnd()) {
            throw new Exception\ValidationException(
                [
                    self::ERROR_INVALID_START_END
                        => 'Serial No - Start must be less than or equal to Serial No - End'
                ]
            );
        } elseif (($command->getSerialNoEnd() - $command->getSerialNoStart()) > self::MAX_DIFF) {
            throw new Exception\ValidationException(
                [
                    self::ERROR_MAX_DIFF_EXCEEDED
                        => 'Difference between Serial No - Start and End must be less than or equal to ' . self::MAX_DIFF
                ]
            );
        }

        // find all existing records
        $results = $this->getRepo()->fetchUsingSerialNoStartEnd($command);

        $status = $this->getRepo()->getRefdataReference($command->getStatus());

        for ($i = $command->getSerialNoStart(); $i <= $command->getSerialNoEnd(); $i++) {
            if (isset($results[$i])) {
                // update existing record
                $irfoPermitStock = $results[$i];
            } else {
                // create new record
                $irfoCountry = $this->getRepo()->getReference(IrfoCountry::class, $command->getIrfoCountry());
                $irfoPermitStock = new IrfoPermitStock($i, $command->getValidForYear(), $irfoCountry);
            }

            // overwrite with updated status
            $irfoPermitStock->setStatus($status);

            // save
            $this->getRepo()->save($irfoPermitStock);
        }

        $result = new Result();
        $result->addMessage('IRFO Permit Stock created successfully');

        return $result;
    }
}
