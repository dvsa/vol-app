<?php

/**
 * Create Variation
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Command\Bus\CreateVariation as CreateVariationCmd;

/**
 * Create Variation
 */
final class CreateVariation extends AbstractCommandHandler
{
    protected $repoServiceName = 'Bus';

    public function handleCommand(CommandInterface $command)
    {
        /** @var CreateVariationCmd $command */
        $bus = $this->createBusRegObject($command);

        $this->getRepo()->save($bus);

        $result = new Result();
        $result->addId('bus', $bus->getId());
        $result->addMessage('Variation created successfully');

        return $result;
    }

    /**
     * @return BusReg
     */
    private function createBusRegObject(CreateVariationCmd $command)
    {
        /** @var BusReg $busReg */
        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        return $busReg->createVariation(
            $this->getRepo()->getRefdataReference(BusReg::STATUS_VAR),
            $this->getRepo()->getRefdataReference(BusReg::STATUS_VAR)
        );
    }
}
