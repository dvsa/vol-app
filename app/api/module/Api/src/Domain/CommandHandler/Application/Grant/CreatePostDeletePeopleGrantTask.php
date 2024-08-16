<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostDeletePeopleGrantTask as CreatePostGrantPeopleTasksCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Class CreatePostDeletePeopleGrantTask
 */
final class CreatePostDeletePeopleGrantTask extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['OrganisationPerson'];

    /**
     * Handle command
     *
     * @param CreatePostGrantPeopleTasksCommand|CommandInterface $command command
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

        $organisation = $application->getLicence()->getOrganisation();

        /** @var OrganisationPerson $organisationPersonRepository */
        $organisationPersonRepository = $this->getRepo('OrganisationPerson');

        $personCount = $organisationPersonRepository->fetchCountForOrganisation($organisation->getId());

        if ($personCount == 0) {
            $this->handleSideEffect(
                CreateTask::create(
                    [
                        'category' => Category::CATEGORY_APPLICATION,
                        'subCategory' => $this->getLastPersonTaskSubCategory($organisation),
                        'description' => $this->getLastPersonTaskDescription($organisation),
                        'licence' => $application->getLicence()->getId(),
                    ]
                )
            );
            $this->result->addMessage('Task created as there are no people in the organisation');
        }

        return $this->result;
    }

    /**
     * Get last person task description
     *
     * @param Organisation $organisation organisation
     *
     * @return string
     */
    private function getLastPersonTaskDescription(Organisation $organisation)
    {
        $organisationType = $organisation->getType()->getId();
        if ($organisationType === Organisation::ORG_TYPE_REGISTERED_COMPANY) {
            return 'Last director removed';
        }
        if ($organisationType === Organisation::ORG_TYPE_LLP) {
            return 'Last partner removed';
        }
        return 'Last person removed';
    }

    /**
     * Get last person task sub category
     *
     * @param Organisation $organisation organisation
     *
     * @return int
     */
    private function getLastPersonTaskSubCategory(Organisation $organisation)
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
}
