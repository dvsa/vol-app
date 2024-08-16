<?php

/**
 * Create a team printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TeamPrinter;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter as TeamPrinterEntity;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create a team printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateTeamPrinter extends AbstractCommandHandler
{
    protected $repoServiceName = 'TeamPrinter';

    public function handleCommand(CommandInterface $command)
    {
        $this->checkIfPrinterExceptionExists($command);

        $teamPrinter = new TeamPrinterEntity(
            $this->getRepo()->getReference(TeamEntity::class, $command->getTeam()),
            $this->getRepo()->getReference(PrinterEntity::class, $command->getPrinter())
        );
        if ($command->getSubCategory()) {
            $teamPrinter->setSubCategory(
                $this->getRepo()->getReference(SubCategoryEntity::class, $command->getSubCategory())
            );
        }
        if ($command->getUser()) {
            $teamPrinter->setUser(
                $this->getRepo()->getReference(UserEntity::class, $command->getUser())
            );
        }

        $this->getRepo()->save($teamPrinter);

        $result = new Result();
        $result->addId('team', $teamPrinter->getId());
        $result->addMessage('Printer exception created successfully');

        return $result;
    }

    /**
     * Check whether a printer exception with a such combination of user (team), category and
     * subcategory already exist
     */
    protected function checkIfPrinterExceptionExists($command)
    {
        if (count($this->getRepo()->fetchByDetails($command))) {
            throw new ValidationException(
                [
                    TeamPrinterEntity::ERROR_PRINTER_EXCEPTION_EXISTS =>
                        'There is already a printer rule for this combination of user' .
                        ', category and sub-category'
                ]
            );
        }
    }
}
