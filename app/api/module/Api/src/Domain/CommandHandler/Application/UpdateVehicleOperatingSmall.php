<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleOperatingSmall as UpdateVehicleOperatingSmallCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateVehicleOperatingSmall extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var $application ApplicationEntity
         * @var $command UpdateVehicleOperatingSmallCommand
         */
        $applicationId = $command->getId();
        $application = $this->getRepo()->fetchById($applicationId, AbstractQuery::HYDRATE_OBJECT, $command->getVersion());

        $application->setPsvOperateSmallVhl($command->getPsvOperateSmallVhl());

        $this->getRepo()->save($application);

        $this->result->merge(
            $this->handleSideEffect(
                UpdateApplicationCompletionCmd::create(
                    ['id' => $application->getId(), 'section' => 'vehicleOperatingSmall']
                )
            )
        );

        $this->result->addId('Application', $applicationId);
        $this->result->addMessage("vehicle operating small updated");
        return $this->result;
    }
}
