<?php

/**
 * Update Stops
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops as UpdateStopsCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Stops
 */
final class UpdateStops extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Bus';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateStopsCmd $command */
        /** @var BusReg $busReg */

        $result = new Result();

        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $busReg->updateStops(
            $command->getUseAllStops(),
            $command->getHasManoeuvre(),
            $command->getManoeuvreDetail(),
            $command->getNeedNewStop(),
            $command->getNewStopDetail(),
            $command->getHasNotFixedStop(),
            $command->getNotFixedStopDetail(),
            $this->getRepo()->getRefdataReference($command->getSubsidised()),
            $command->getSubsidyDetail()
        );

        $this->getRepo()->save($busReg);
        $result->addMessage('Saved successfully');
        $result->addId('id', $busReg->getId());
        return $result;
    }
}
