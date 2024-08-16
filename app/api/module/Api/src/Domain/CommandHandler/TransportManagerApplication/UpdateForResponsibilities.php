<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * UpdateForResponsibilities Transport Manager Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateForResponsibilities extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateForResponsibilities $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $transportManagerApplication =  $this->updateTransportManagerApplication($command);
        $this->getRepo()->save($transportManagerApplication);

        return $this->result
            ->addMessage('Transport Manager Application updated')
            ->addId('transportManagerApplication', $transportManagerApplication->getId());
    }

    /**
     * Update Transport Manager Application
     *
     * @param \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateForResponsibilities $command Command
     *
     * @return Entity\Tm\TransportManagerApplication
     */
    protected function updateTransportManagerApplication($command)
    {
        /** @var Entity\Tm\TransportManagerApplication $transportManagerApplication */
        $transportManagerApplication = $this->getRepo()->
            fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $transportManagerApplication->updateTransportManagerApplicationFull(
            $this->getRepo()->getRefdataReference($command->getTmType()),
            $command->getIsOwner(),
            $command->getHasUndertakenTraining(),
            $command->getHoursMon(),
            $command->getHoursTue(),
            $command->getHoursWed(),
            $command->getHoursThu(),
            $command->getHoursFri(),
            $command->getHoursSat(),
            $command->getHoursSun(),
            $command->getAdditionalInformation(),
            $this->getRepo()->getRefdataReference($command->getTmApplicationStatus())
        );
        return $transportManagerApplication;
    }
}
