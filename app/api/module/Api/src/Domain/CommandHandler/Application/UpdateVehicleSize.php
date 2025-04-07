<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleSize as UpdateVehicleSizeCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateVehicleSize extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var $application ApplicationEntity
         * @var $command UpdateVehicleSizeCommand
         */
        $applicationId = $command->getId();
        $application = $this->getRepo()->fetchById($applicationId, AbstractQuery::HYDRATE_OBJECT, $command->getVersion());

        $application->updatePsvVehicleSize($this->getRepo()->getRefdataReference($command->getPsvVehicleSize()));

        $this->getRepo()->save($application);

        $this->result->merge(
            $this->handleSideEffect(
                UpdateApplicationCompletionCmd::create(
                    ['id' => $application->getId(), 'section' => 'vehiclesSize']
                )
            )
        );

        $this->result->addId('Application', $applicationId);
        $this->result->addMessage("vehicle size updated");
        return $this->result;
    }
}
