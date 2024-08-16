<?php

/**
 * Create Transport Manager Appplication
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TransportManagerApplicationEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\CreateForResponsibilities as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Create Transport Manager Appplication
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateForResponsibilities extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = ['Application', 'TransportManagerLicence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $application = $this->validateTransportManagerApplication($command);

        $tmApplication = $this->createTransportManagerApplicationObject($command, $application->getLicence()->getId());

        $this->getRepo()->save($tmApplication);

        $result->addId('transportManagerApplication', $tmApplication->getId());
        $result->addMessage('Transport Manager Application created successfully');

        $completionData = ['id' => $application->getId(), 'section' => 'transportManagers'];
        $result->merge($this->handleSideEffect(UpdateApplicationCompletion::create($completionData)));

        return $result;
    }

    private function validateTransportManagerApplication($command)
    {
        try {
            $application = $this->getRepo('Application')->fetchWithLicence($command->getApplication());
        } catch (\Exception) {
            throw new ValidationException(
                [
                    'application' =>  'The application ID is not valid'
                ]
            );
        }
        $licenceType = $application->getLicenceType()->getId();
        if (
            $licenceType === LicenceEntity::LICENCE_TYPE_RESTRICTED ||
            $licenceType === LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED
        ) {
            throw new ValidationException(
                [
                    'application' =>  'A transport manager cannot be added to a restricted licence'
                ]
            );
        }
        $tmApplication = $this->getRepo()
            ->fetchByTmAndApplication($command->getTransportManager(), $command->getApplication());
        if ($tmApplication) {
            throw new ValidationException(
                [
                    'application' =>  'The transport manager is already linked to this application'
                ]
            );
        }
        if (
            $application->getStatus()->getId() !== ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED  &&
            $application->getStatus()->getId() !== ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION
        ) {
            throw new ValidationException(
                [
                    'application' =>
                        'You can add a transport manager to a not submitted or under consideration application only'
                ]
            );
        }
        return $application;
    }

    /**
     * @param Cmd $command
     * @param int $licenceId
     * @return TransportManagerApplicationEntity
     */
    private function createTransportManagerApplicationObject($command, $licenceId)
    {
        $tmApplication = new TransportManagerApplicationEntity();

        $tmLicences = $this->getRepo('TransportManagerLicence')
            ->fetchByTmAndLicence($command->getTransportManager(), $licenceId);

        $tmApplication->updateTransportManagerApplication(
            $this->getRepo()->getReference(ApplicationEntity::class, $command->getApplication()),
            $this->getRepo()->getReference(TransportManagerEntity::class, $command->getTransportManager()),
            count($tmLicences) ? 'U' : 'A',
            $this->getRepo()->getRefdataReference(TransportManagerApplicationEntity::STATUS_POSTAL_APPLICATION)
        );
        return $tmApplication;
    }
}
