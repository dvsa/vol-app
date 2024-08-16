<?php

/**
 * Update Ta Authority
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateTaAuthority as UpdateTaAuthorityCmd;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * Update Ta Authority
 */
final class UpdateTaAuthority extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Bus';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateTaAuthorityCmd $command */
        /** @var BusReg $busReg */

        $result = new Result();

        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $busReg->updateTaAuthority(
            $command->getStoppingArrangements()
        );

        $la = $this->processLocalAuthority($command->getLocalAuthoritys());
        $busReg->setLocalAuthoritys($la);

        $ta = $this->processTrafficAreas($command->getTrafficAreas());
        $busReg->setTrafficAreas($ta);

        $this->getRepo()->save($busReg);
        $result->addMessage('Saved successfully');
        return $result;
    }

    /**
     * Returns collection of local authorities.
     *
     * @param array $localAuthority
     * @return ArrayCollection
     */
    private function processLocalAuthority($localAuthority)
    {
        $result = new ArrayCollection();
        if (!empty($localAuthority)) {
            foreach ($localAuthority as $la) {
                $result->add($this->getRepo()->getReference(LocalAuthorityEntity::class, $la));
            }
        }
        return $result;
    }

    /**
     * Returns collection of traffic areas.
     *
     * @param array $trafficAreas
     * @return ArrayCollection
     */
    private function processTrafficAreas($trafficAreas)
    {
        $result = new ArrayCollection();
        if (!empty($trafficAreas)) {
            foreach ($trafficAreas as $ta) {
                $result->add($this->getRepo()->getReference(TrafficAreaEntity::class, $ta));
            }
        }
        return $result;
    }
}
