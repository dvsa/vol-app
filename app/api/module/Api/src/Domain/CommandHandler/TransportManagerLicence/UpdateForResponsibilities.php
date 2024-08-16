<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerLicence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * UpdateForResponsibilities Transport Manager Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateForResponsibilities extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerLicence';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $transportManagerLicence =  $this->updateTransportManagerLicence($command);
        $this->getRepo()->save($transportManagerLicence);

        return $this->result
            ->addMessage("Transport Manager Licence updated")
            ->addId('transportManagerLicence', $transportManagerLicence->getId());
    }

    /**
     * Update Transport Manager Licence
     *
     * @param \Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities $command Command
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence
     */
    protected function updateTransportManagerLicence($command)
    {
        /** @var \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence $transportManagerLicence */
        $transportManagerLicence = $this->getRepo()->
            fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $transportManagerLicence->updateTransportManagerLicence(
            $this->getRepo()->getRefdataReference($command->getTmType()),
            $command->getHoursMon(),
            $command->getHoursTue(),
            $command->getHoursWed(),
            $command->getHoursThu(),
            $command->getHoursFri(),
            $command->getHoursSat(),
            $command->getHoursSun(),
            $command->getAdditionalInformation(),
            $command->getIsOwner()
        );
        return $transportManagerLicence;
    }
}
