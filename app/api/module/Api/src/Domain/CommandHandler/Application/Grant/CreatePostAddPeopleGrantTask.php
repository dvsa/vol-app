<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostAddPeopleGrantTask as CreatePostAddPeopleGrantTaskCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Class CreatePostDeletePeopleGrantTask
 */
final class CreatePostAddPeopleGrantTask extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * Handle command
     *
     * @param CreatePostAddPeopleGrantTaskCommand|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchById($command->getApplicationId());

        $variationType = $application->getVariationType();
        if ($variationType === null or $variationType->getId() !== Application::VARIATION_TYPE_DIRECTOR_CHANGE) {
            return $this->result;
        }

        $applicationOrgPeople = $application->getApplicationOrganisationPersonsAdded();

        if ($applicationOrgPeople->count() === 0) {
            return $this->result;
        }

        $this->result->addMessage('Task created as new people were added');

        $organisation = $application->getLicence()->getOrganisation();

        $this->handleSideEffect(
            CreateTask::create(
                [
                    'category' => Category::CATEGORY_APPLICATION,
                    'subCategory' => $this->getTaskSubCategory($organisation),
                    'description' => $this->getTaskDescription($application->getLicence()->getOrganisation()),
                    'licence' => $application->getLicence()->getId(),
                    'urgent' => $this->isTaskUrgent($application) ? 'Y' : 'N',
                ]
            )
        );
        return $this->result;
    }

    /**
     * Get task description
     *
     * @param Organisation $organisation organisation
     *
     * @return string
     */
    private function getTaskDescription(Organisation $organisation)
    {
        $organisationType = $organisation->getType()->getId();
        if ($organisationType === Organisation::ORG_TYPE_REGISTERED_COMPANY) {
            return 'Add director(s)';
        }
        if ($organisationType === Organisation::ORG_TYPE_LLP) {
            return 'Add partner(s)';
        }
        return 'Add responsible person(s)';
    }

    /**
     * Get task sub category
     *
     * @param Organisation $organisation organisation
     *
     * @return int
     */
    private function getTaskSubCategory(Organisation $organisation)
    {
        $organisationType = $organisation->getType()->getId();
        if ($organisationType === Organisation::ORG_TYPE_REGISTERED_COMPANY) {
            return Category::TASK_SUB_CATEGORY_DIRECTOR_CHANGE_DIGITAL;
        }
        if ($organisationType === Organisation::ORG_TYPE_LLP) {
            return Category::TASK_SUB_CATEGORY_PARTNER_CHANGE_DIGITAL;
        }
        return Category::TASK_SUB_CATEGORY_PERSON_CHANGE_DIGITAL;
    }

    /**
     * Is task urgent?
     *
     * @param Application $application application
     *
     * @return bool
     */
    private function isTaskUrgent(Application $application)
    {
        if ($application->getPrevConviction() !== 'N') {
            return true;
        }
        $financialAnswers = [
            $application->getBankrupt(),
            $application->getLiquidation(),
            $application->getReceivership(),
            $application->getAdministration(),
            $application->getDisqualified(),
        ];
        foreach ($financialAnswers as $answer) {
            if ($answer !== 'N') {
                return true;
            }
        }
        return false;
    }
}
