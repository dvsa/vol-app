<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Variation\ResetVariation as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\ApplicationResetTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation as CreateVariationCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

final class ResetVariation extends AbstractCommandHandler implements TransactionedInterface
{
    use ApplicationResetTrait;

    private Repository\Application $applicationRepo;
    private Repository\ApplicationOperatingCentre $applicationOperatingCentreRepo;

    /**
     * @throws RequiresConfirmationException
     * @throws RuntimeException
     */
    public function handleCommand(Cmd|CommandInterface $command): Result
    {
        $application = $this->applicationRepo->fetchUsingId($command);

        $this->validate($command);

        $count = $this->closeTasks($application);
        $this->result->addMessage($count . ' task(s) closed');

        $licenceId = $application->getLicence()->getId();
        $receivedDate = $application->getReceivedDate();
        $appliedVia = $application->getAppliedVia();

        $aocCount = $this->removeAssociationOfApplicationAndOperatingCentres($application);
        $this->result->addMessage($aocCount . ' application operating centres associations removed');

        $this->applicationRepo->delete($application);
        $this->result->addMessage('Variation removed');

        $this->result->merge(
            $this->createNewVariation($licenceId, $receivedDate, $appliedVia)
        );

        return $this->result;
    }

    private function createNewVariation(int $licenceId, mixed $receivedDate, RefData $appliedVia): Result
    {
        $data = [
            'id' => $licenceId,
            'appliedVia' => $appliedVia->getId(),
            'receivedDate' => $this->processReceivedDate($receivedDate),
        ];

        return $this->handleSideEffect(
            CreateVariationCommand::create($data)
        );
    }

    /**
     * If the user is required to confirm this change, throw an exception to the front end to indicate as such
     *
     * @throws RequiresConfirmationException
     */
    private function validate(Cmd $command): void
    {
        if ($command->getConfirm() !== false) {
            return;
        }

        throw new RequiresConfirmationException(
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
        $this->applicationOperatingCentreRepo = $container->get('RepositoryServiceManager')
            ->get('ApplicationOperatingCentre');

        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
