<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as WindowEntity;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Create as CreateWindowCmd;

/**
 * Create an IRHP Permit Window
 *
 * @author Andy Newton
 */
class Create extends AbstractCommandHandler
{
    use IrhpPermitWindowTrait;

    protected $repoServiceName = 'IrhpPermitWindow';
    protected $extraRepos = ['IrhpPermitStock'];

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var CreateWindowCmd $command
         */
        // If there are overlapping windows.
        if ($this->numberOfOverlappingWindows($command->getIrhpPermitStock(), $command->getStartDate(), $command->getEndDate()) > 0) {
            throw new ValidationException(['The dates overlap with another window for this Permit stock']);
        }

        /** @var IrhpPermitStock $irhpPermitStock */
        $irhpPermitStock = $this->getRepo('IrhpPermitStock')->fetchById($command->getIrhpPermitStock());

        $this->validateStockRanges($irhpPermitStock);

        $window = WindowEntity::create(
            $irhpPermitStock,
            $command->getStartDate(),
            $command->getEndDate()
        );

        $this->getRepo()->save($window);

        $this->result->addId('IrhpPermitWindow', $window->getId());
        $this->result->addMessage("IRHP Permit Window '{$window->getId()}' created");

        return $this->result;
    }
}
