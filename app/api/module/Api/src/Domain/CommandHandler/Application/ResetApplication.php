<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\ResetApplication as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\ApplicationResetTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication as CreateApplicationCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

final class ResetApplication extends AbstractCommandHandler implements TransactionedInterface
{
    use ApplicationResetTrait;

    private Repository\Application $applicationRepo;
    private Repository\Licence $licenceRepo;
    private Repository\ApplicationOperatingCentre $applicationOperatingCentreRepo;

    /**
     * @throws RequiresConfirmationException
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function handleCommand(Cmd|CommandInterface $command): Result
    {
        $result = new Result();

        /** @var Application $application */
        $application = $this->applicationRepo->fetchUsingId($command);

        $this->validate($command, $application);

        $licence = $application->getLicence();

        $receivedDate = $application->getReceivedDate();
        $appliedVia = $application->getAppliedVia();

        // Need to grab this now before removing the licence
        $organisation = $application->getLicence()->getOrganisation();

        $count = $this->closeTasks($application);
        $result->addMessage($count . ' task(s) closed');

        $this->licenceRepo->delete($licence);
        $result->addMessage('Licence removed');

        $aocCount = $this->removeAssociationOfApplicationAndOperatingCentres($application);
        $result->addMessage($aocCount . ' application operating centres associations removed');

        $this->applicationRepo->delete($application);
        $result->addMessage('Application removed');

        $result->merge(
            $this->createNewApplication($command, $organisation, $receivedDate, $appliedVia)
        );

        return $result;
    }

    private function createNewApplication(
        Cmd $command,
        Organisation $organisation,
        mixed $receivedDate = null,
        RefData $appliedVia = null
    ): Result {
        $data = $command->getArrayCopy();
        $data['organisation'] = $organisation->getId();
        $data['appliedVia'] = $appliedVia->getId();
        $data['receivedDate'] = $this->processReceivedDate($receivedDate);

        return $this->handleSideEffect(
            CreateApplicationCommand::create($data)
        );
    }

    /**
     * @throws RequiresConfirmationException
     * @throws ValidationException
     */
    private function validate(Cmd $command, Application $application): void
    {
        if ($command->getConfirm() !== false) {
            return;
        }

        // Before we tell the UI we need confirmation, we better validate the values
        $application->validateTol(
            $command->getNiFlag(),
            $this->applicationRepo->getRefdataReference($command->getOperatorType()),
            $this->applicationRepo->getRefdataReference($command->getLicenceType()),
            $this->applicationRepo->getRefDataReference($command->getVehicleType()),
            $command->getLgvDeclarationConfirmation()
        );

        // Tell the UI we need confirmation
        throw new Exception\RequiresConfirmationException(
            'Updating these elements requires confirmation',
            Application::ERROR_REQUIRES_CONFIRMATION
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->applicationRepo = $container->get('RepositoryServiceManager')
            ->get('Application');
        $this->licenceRepo = $container->get('RepositoryServiceManager')
            ->get('Licence');
        $this->applicationOperatingCentreRepo = $container->get('RepositoryServiceManager')
            ->get('ApplicationOperatingCentre');

        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
