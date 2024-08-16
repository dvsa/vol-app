<?php

/**
 * Create a printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Printer;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create a printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreatePrinter extends AbstractCommandHandler
{
    protected $repoServiceName = 'Printer';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $printer = new PrinterEntity();
        $printer->setPrinterName($command->getPrinterName());
        $printer->setDescription($command->getDescription());

        $this->getRepo()->save($printer);

        $result = new Result();
        $result->addId('printer', $printer->getId());
        $result->addMessage('Printer created successfully');

        return $result;
    }
}
