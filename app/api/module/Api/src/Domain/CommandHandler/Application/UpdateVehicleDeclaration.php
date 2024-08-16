<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleDeclaration as Command;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * UpdateVehicleDeclaration
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateVehicleDeclaration extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // This data is validated by the front end form, if that is incorrect/fails then the
        // updateApplicationCompletion will handle it by setting the section to incomplete

        $application->setPsvWhichVehicleSizes($this->getRepo()->getRefdataReference($command->getPsvVehicleSize()));
        $application->setPsvNoSmallVhlConfirmation($command->getPsvNoSmallVhlConfirmation());
        $application->setPsvOperateSmallVhl($command->getPsvOperateSmallVhl());
        $application->setPsvSmallVhlConfirmation($command->getPsvSmallVhlConfirmation());
        $application->setPsvSmallVhlNotes($command->getPsvSmallVhlNotes());
        $application->setPsvMediumVhlConfirmation($command->getPsvMediumVhlConfirmation());
        $application->setPsvMediumVhlNotes($command->getPsvMediumVhlNotes());
        $application->setPsvLimousines($command->getPsvLimousines());
        $application->setPsvNoLimousineConfirmation($command->getPsvNoLimousineConfirmation());
        $application->setPsvOnlyLimousinesConfirmation($command->getPsvOnlyLimousinesConfirmation());
        $this->getRepo()->save($application);

        $result = $this->handleSideEffect(
            UpdateApplicationCompletionCmd::create(
                ['id' => $application->getId(), 'section' => 'vehiclesDeclarations']
            )
        );

        $result->addMessage("Application ID {$application->getId()} vehicle declaration updated.");
        return $result;
    }
}
