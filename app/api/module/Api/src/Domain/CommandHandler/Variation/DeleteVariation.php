<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadVariationTypeException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Transfer\Command\Application\DeletePeople;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocuments;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\DeletePreviousConviction;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteVariation as DeleteVariationCommand;

/**
 * Class GrantDirectorChange
 */
class DeleteVariation extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * @param DeleteVariationCommand|CommandInterface $command GrantDirectorChange Command
     *
     * @return Result
     * @throws BadVariationTypeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $application = $this->getApplication($command);

        $this->guardAgainstIncorrectVariationType($application);

        $this->deletePersons($application);
        $this->deleteDocuments($application);
        $this->deletePreviousConvictions($application);
        $this->deleteApplication($application);
        return $this->result;
    }

    /**
     * Get the variation to be deleted
     *
     * @param DeleteVariationCommand|CommandInterface $command command
     *
     * @return Application
     */
    private function getApplication(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchById($command->getId());
        return $application;
    }

    /**
     * guardAgainstIncorrectVariationType
     *
     * @param Application $application application
     *
     * @throws BadVariationTypeException
     * @return void
     */
    private function guardAgainstIncorrectVariationType(Application $application)
    {
        if (!$application->getIsVariation()) {
            throw new BadVariationTypeException("Applications can not be deleted");
        }

        if ($application->getVariationType() === null) {
            throw new BadVariationTypeException("Standard variations can not be deleted");
        }

        $variationType = $application->getVariationType()->getId();
        if ($variationType !== Application::VARIATION_TYPE_DIRECTOR_CHANGE) {
            throw new BadVariationTypeException("Variations of type '{$variationType}' can not be deleted");
        }
    }

    /**
     * Delete the applications person's and applicationOrganisationPersons
     *
     * @param Application $application application
     *
     * @return void
     */
    private function deletePersons(Application $application)
    {
        $personIds = [];
        /** @var ApplicationOrganisationPerson $applicationOrganisationPerson */
        foreach ($application->getApplicationOrganisationPersons() as $applicationOrganisationPerson) {
            $personIds[] = $applicationOrganisationPerson->getPerson()->getId();
        }
        $this->handleSideEffect(DeletePeople::create(['id' => $application->getId(), 'personIds' => $personIds]));
    }

    /**
     * Delete the application's documents
     *
     * @param Application $application application
     *
     * @return void
     */
    private function deleteDocuments(Application $application)
    {
        $documentIds = [];
        /** @var Document $documents */
        foreach ($application->getDocuments() as $documents) {
            $documentIds[] = $documents->getId();
        }
        $this->handleSideEffect(DeleteDocuments::create(['ids' => $documentIds]));
    }

    /**
     * Delete the application's previous convictions
     *
     * @param Application $application application
     *
     * @return void
     */
    private function deletePreviousConvictions(Application $application)
    {
        $previousConvictionIds = [];
        /** @var PreviousConviction $previousConviction */
        foreach ($application->getPreviousConvictions() as $previousConviction) {
            $previousConvictionIds[] = $previousConviction->getId();
        }
        $this->handleSideEffect(DeletePreviousConviction::create(['ids' => $previousConvictionIds]));
    }

    /**
     * Delete the application
     *
     * @param Application $application application
     *
     * @return void
     */
    private function deleteApplication(Application $application)
    {
        $this->getRepo()->delete($application);
        $this->result->addId('application ' . $application->getId(), $application->getId());
        $this->result->addMessage('Application with id ' . $application->getId() . ' was deleted');
    }
}
