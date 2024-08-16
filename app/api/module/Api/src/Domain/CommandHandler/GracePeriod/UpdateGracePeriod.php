<?php

/**
 * Update Grace Period
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GracePeriod;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Update Grace Period
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class UpdateGracePeriod extends AbstractCommandHandler
{
    protected $repoServiceName = 'GracePeriod';

    public function handleCommand(CommandInterface $command)
    {
        $gracePeriod = $this->getRepo()
            ->fetchById(
                $command->getId(),
                \Doctrine\ORM\Query::HYDRATE_OBJECT,
                $command->getVersion()
            );

        $gracePeriod->setStartDate(new \DateTime($command->getStartDate()));
        $gracePeriod->setEndDate(new \DateTime($command->getEndDate()));
        $gracePeriod->setDescription($command->getDescription());

        $this->getRepo()->save($gracePeriod);

        $result = new Result();
        $result->addId('graceperiod', $gracePeriod->getId());
        $result->addMessage('Grace period updated successfully');

        return $result;
    }
}
