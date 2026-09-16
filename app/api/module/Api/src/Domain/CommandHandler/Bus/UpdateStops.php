<?php

/**
 * Update Stops
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
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
    #[\Override]
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

        $areaIds = $command->getSubsidyTrafficAreas();
        $areas = new ArrayCollection();
        foreach ($areaIds as $id) {
            $areas->add($this->getRepo()->getReference(TrafficArea::class, $id));
        }

        $authorities = new ArrayCollection();
        foreach ($command->getSubsidyLocalAuthorities() as $id) {
            $authority = $this->getRepo()->getReference(LocalAuthority::class, $id);
            if (!in_array($authority->getTrafficArea()->getId(), $areaIds, true)) {
                throw new ValidationException([
                    'subsidyLocalAuthorities' => [
                        'Select local authorities within the TAOs providing subsidies',
                    ],
                ]);
            }
            $authorities->add($authority);
        }
        $busReg->setSubsidyTrafficAreas($areas);
        $busReg->setSubsidyLocalAuthorities($authorities);

        $this->getRepo()->save($busReg);
        $result->addMessage('Saved successfully');
        $result->addId('id', $busReg->getId());
        return $result;
    }
}
