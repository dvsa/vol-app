<?php

/**
 * Update Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Update Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateVehicles extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $hasEnteredReg = $command->getHasEnteredReg();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // If we are entering vehicles, and it is not a partial save then we need to have some active vehicles
        if (
            $hasEnteredReg == 'Y' && !$command->getPartial()
            && $application->getActiveVehicles()->count() < 1
        ) {
            throw new ValidationException(
                [
                    'vehicles' => [
                        ApplicationEntity::ERROR_NO_VEH_ENTERED => 'No vehicles added'
                    ]
                ]
            );
        }

        $application->setHasEnteredReg($hasEnteredReg);
        $this->getRepo()->save($application);
        $result->addMessage('Application updated');

        $completionData = ['id' => $command->getId(), 'section' => 'vehicles'];
        $result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($completionData)));

        return $result;
    }
}
