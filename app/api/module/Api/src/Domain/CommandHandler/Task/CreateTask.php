<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Task;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Exception;

final class CreateTask extends AbstractCommandHandler
{
    protected $extraRepos = [
        Repository\SystemParameter::class,
        Repository\Task::class,
        Repository\TaskAllocationRule::class,
    ];

    protected array $alphaNumericTranslations = [
        0 => 'Z',
        1 => 'O',
        2 => 'T',
        3 => 'T',
        4 => 'F',
        5 => 'F',
        6 => 'S',
        7 => 'S',
        8 => 'E',
        9 => 'N'
    ];

    /**
     * @param \Dvsa\Olcs\Transfer\Command\Task\CreateTask|CommandInterface $command
     * @return Result
     * @throws ORMException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $task = $this->createTaskObject($command);

        if ($task->getAssignedToUser() === null && $task->getAssignedToTeam() === null) {
            $this->autoAssignTask($task);
        }

        $this->getRepo(Repository\Task::class)->save($task);

        $result = new Result();
        $result->addId('task', $task->getId());
        if ($task->getAssignedToUser() !== null) {
            $result->addId('assignedToUser', $task->getAssignedToUser()->getId());
        }
        $result->addMessage('Task created successfully');

        return $result;
    }

    /**
     * @throws RuntimeException|ORMException
     */
    private function autoAssignTask(Entity\Task\Task $task): void
    {
        if ($task->getLicence() !== null) {
            $rules = $this->getRulesBasedOnLicence($task);
            $useAlphaSplit = true;
        } else {
            $rules = $this->getRulesBasedOnCategory($task);
            $useAlphaSplit = false;
        }

        /**
         * Multiple rules are just as useless as no rules according to AC
         */
        if (count($rules) !== 1) {
            $this->assignToDefault($task);
        } else {
            $this->assignByRule($task, $rules[0], $useAlphaSplit);
        }
    }

    /**
     * Fall back on system configuration to populate user and team
     * @throws RuntimeException|ORMException
     */
    private function assignToDefault(Entity\Task\Task $task): void
    {
        $repo = $this->getRepo(Repository\SystemParameter::class);

        $teamId = $repo->fetchValue('task.default_team');
        if ($teamId !== null) {
            $task->setAssignedToTeam($this->getRepo(Repository\Task::class)->getReference(Entity\User\Team::class, $teamId));
        }

        $userId = $repo->fetchValue('task.default_user');
        if ($userId !== null) {
            $task->setAssignedToUser($this->getRepo(Repository\Task::class)->getReference(Entity\User\User::class, $userId));
        }
    }

    protected function assignByRule(Entity\Task\Task $task, Entity\Task\TaskAllocationRule $rule, bool $useAlphaSplit): void
    {
        $task->setAssignedToTeam($rule->getTeam());
        if ($rule->getUser() !== null) {
            $task->setAssignedToUser($rule->getUser());
        } elseif ($useAlphaSplit) {
            $this->assignByAlphaSplit($task, $rule);
        }
    }

    protected function assignByAlphaSplit(Entity\Task\Task $task, Entity\Task\TaskAllocationRule $rule): void
    {
        $taskAlphaSplits = $rule->getTaskAlphaSplits();
        if ($taskAlphaSplits === null) {
            return;
        }
        $letter = $this->getLetterForAlphaSplit($task);
        if (is_numeric($letter)) {
            $letter = $this->alphaNumericTranslations[(int) $letter];
        }
        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->contains('letters', $letter));
        $alphaSplits = $taskAlphaSplits->matching($criteria);
        if (count($alphaSplits) !== 1) {
            return;
        }
        $task->setAssignedToUser($alphaSplits->first()->getUser());
    }

    /**
     * @throws RuntimeException
     */
    protected function getRulesBasedOnCategory(Entity\Task\Task $task): array
    {
        return $this
            ->getRepo(Repository\TaskAllocationRule::class)
            ->fetchByParametersWithFallbackWhenSubCategoryNotFound(
                $task->getCategory()->getId(),
                $task->getSubCategory()->getId()
            );
    }

    /**
     * @throws RuntimeException
     */
    protected function getRulesBasedOnLicence(Entity\Task\Task $task): array
    {
        $repo = $this->getRepo(Repository\TaskAllocationRule::class);

        $licence = $task->getLicence();
        $app = $task->getApplication();

        $licenceTrafficArea = $licence->getTrafficAreaForTaskAllocation();
        $trafficArea = (
            $licenceTrafficArea !== null
            ? $licenceTrafficArea->getId()
            : null
        );

        //  define operator Type
        $operatorType = null;

        $goodsOrPsv = $licence->getGoodsOrPsv();
        if ($goodsOrPsv === null && $app !== null) {
            $goodsOrPsv = $app->getGoodsOrPsv();
        }

        if ($goodsOrPsv === null) {
            $newApplications = $licence->getNewApplications();
            $app = $newApplications->first();
            $goodsOrPsv = $app->getGoodsOrPsv();
        }

        if ($goodsOrPsv !== null) {
            $operatorType = $goodsOrPsv->getId();
        }

        // Goods Licence
        if ($operatorType === Entity\Licence\Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $rules = $repo->fetchByParametersWithFallbackWhenSubCategoryNotFound(
                $task->getCategory()->getId(),
                $task->getSubCategory()->getId(),
                Entity\Licence\Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                $trafficArea,
                $licence->getOrganisation()->isMlh()
            );
            if (count($rules) >= 1) {
                return $rules;
            }
        }

        // PSV licence or no rules found for Goods Licence
        // search rules by category, operator type and traffic area
        $rules = $repo->fetchByParametersWithFallbackWhenSubCategoryNotFound(
            $task->getCategory()->getId(),
            $task->getSubCategory()->getId(),
            $operatorType,
            $trafficArea
        );
        if (count($rules) >= 1) {
            return $rules;
        }

        // search rules by category and traffic area
        $rules = $repo->fetchByParametersWithFallbackWhenSubCategoryNotFound(
            $task->getCategory()->getId(),
            $task->getSubCategory()->getId(),
            null,
            $trafficArea
        );
        if (count($rules) >= 1) {
            return $rules;
        }

        // search rules by category and operator type
        $rules = $repo->fetchByParametersWithFallbackWhenSubCategoryNotFound(
            $task->getCategory()->getId(),
            $task->getSubCategory()->getId(),
            $operatorType
        );
        if (count($rules) >= 1) {
            return $rules;
        }

        // search rules by category only
        return $repo->fetchByParametersWithFallbackWhenSubCategoryNotFound($task->getCategory()->getId(), $task->getSubCategory()->getId());
    }

    protected function getLetterForAlphaSplit(Entity\Task\Task $task): string
    {
        $letter = '';
        $organisation = $task->getLicence()->getOrganisation();
        switch ($organisation->getType()) {
            case Entity\Organisation\Organisation::ORG_TYPE_REGISTERED_COMPANY:
            case Entity\Organisation\Organisation::ORG_TYPE_LLP:
            case Entity\Organisation\Organisation::ORG_TYPE_OTHER:
                $companyName = strtoupper($organisation->getName());
                if (strlen($companyName) > 4 && str_starts_with($companyName, 'THE ')) {
                    $companyName = substr($companyName, 4);
                }
                $letter = substr($companyName, 0, 1);
                break;
            case Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER:
                $organisationPerson = $organisation->getOrganisationPersons()->first();
                if ($organisationPerson) {
                    $letter = strtoupper(substr((string) $organisationPerson->getPerson()->getFamilyName(), 0, 1));
                }
                break;
            case Entity\Organisation\Organisation::ORG_TYPE_PARTNERSHIP:
                $organisationPersons = $organisation->getOrganisationPersons();
                $criteria = Criteria::create();
                $criteria->orderBy(['id' => Criteria::ASC]);
                // get first person
                $organisationPerson = $organisationPersons->matching($criteria)->first();
                // if first person exists
                if ($organisationPerson) {
                    $letter = strtoupper(substr((string) $organisationPerson->getPerson()->getFamilyName(), 0, 1));
                }
                break;
        }

        return $letter;
    }

    /**
     * @return Task
     * @throws ORMException
     * @throws RuntimeException
     * @throws Exception
     */
    private function createTaskObject(CommandInterface $command): Entity\Task\Task
    {
        /** @var Repository\Task $repo */
        $repo = $this->getRepo(Repository\Task::class);

        // Required
        $category = $repo->getCategoryReference($command->getCategory());
        $subCategory = $repo->getSubCategoryReference($command->getSubCategory());

        $task = new Entity\Task\Task($category, $subCategory);

        // Optional relationships
        $userId = (int)$command->getAssignedToUser();
        $task->setAssignedToUser($repo->getReference(Entity\User\User::class, $userId));
        $task->setAssignedToTeam($repo->getTeamReference((int)$command->getAssignedToTeam(), $userId));

        if ($command->getApplication() !== null) {
            $application = $repo->getReference(Entity\Application\Application::class, $command->getApplication());
            $task->setApplication($application);
        }

        if ($command->getLicence() !== null) {
            $Licence = $repo->getReference(Entity\Licence\Licence::class, $command->getLicence());
            $task->setLicence($Licence);
        }

        if ($command->getBusReg() !== null) {
            $task->setBusReg(
                $repo->getReference(Entity\Bus\BusReg::class, $command->getBusReg())
            );
        }

        if ($command->getCase() !== null) {
            $task->setCase(
                $repo->getReference(Entity\Cases\Cases::class, $command->getCase())
            );
        }

        if ($command->getSubmission() !== null) {
            $task->setSubmission(
                $repo->getReference(Entity\Submission\Submission::class, $command->getSubmission())
            );
        }

        if ($command->getTransportManager() !== null) {
            $task->setTransportManager(
                $repo->getReference(Entity\Tm\TransportManager::class, $command->getTransportManager())
            );
        }

        if ($command->getSurrender() !== null) {
            $task->setSurrender(
                $repo->getReference(Entity\Surrender::class, $command->getSurrender())
            );
        }

        if ($command->getIrfoOrganisation() !== null) {
            $task->setIrfoOrganisation(
                $repo->getReference(Entity\Organisation\Organisation::class, $command->getIrfoOrganisation())
            );
        }

        if ($command->getIrhpApplication() !== null) {
            $task->setIrhpApplication(
                $repo->getReference(Entity\Permits\IrhpApplication::class, $command->getIrhpApplication())
            );
        }

        if ($command->getAssignedByUser() !== null) {
            $task->setAssignedByUser(
                $repo->getReference(Entity\User\User::class, $command->getAssignedByUser())
            );
        }

        if ($command->getActionDate() !== null) {
            $task->setActionDate(new DateTime($command->getActionDate()));
        } else {
            $task->setActionDate(new DateTime());
        }

        // Task properties
        $task->setDescription($command->getDescription());
        $task->setIsClosed($command->getIsClosed());
        $task->setUrgent($command->getUrgent());
        $task->setMessaging(in_array($command->getMessaging(), ['Y', true]));

        $task->setLastModifiedOn(new DateTime());

        return $task;
    }
}
