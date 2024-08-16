<?php

/**
 * Create DeclareUnfit
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\CreateDeclareUnfit as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Create DeclareUnfit
 */
final class CreateDeclareUnfit extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'TmCaseDecision';

    protected $extraRepos = ['TransportManager'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var CasesEntity $case */
        $case = $this->getCaseEntity($command);
        $transportManager = $case->getTransportManager();

        // create and save a record
        $entity = $this->createEntityObject($command, $case);
        $this->getRepo()->save($entity);

        // update the TM record
        $transportManager->setDisqualificationTmCaseId($case->getId());
        $transportManager->setTmStatus(
            $this->getRepo()->getRefdataReference(ContactDetails::TRANSPORT_MANAGER_STATUS_DISQUALIFIED)
        );

        $this->getRepo('TransportManager')->save($transportManager);

        // create a task
        $taskResult = $this->handleSideEffect(
            $this->createCreateTaskCommand(
                $command,
                $case,
                $transportManager
            )
        );
        $result->merge($taskResult);

        $result->addId('tmCaseDecision', $entity->getId());
        $result->addMessage('Decision created successfully');

        return $result;
    }

    /**
     * Create the
     * @return Entity
     */
    private function createEntityObject(Cmd $command, CasesEntity $case)
    {
        $data = $command->getArrayCopy();

        // set unfitness reasons
        $data['unfitnessReasons'] = array_map(
            fn($unfitnessReasonId) => $this->getRepo()->getRefdataReference($unfitnessReasonId),
            $data['unfitnessReasons']
        );

        if (!empty($data['rehabMeasures'])) {
            // set rehab measures
            $data['rehabMeasures'] = array_map(
                fn($rehabMeasureId) => $this->getRepo()->getRefdataReference($rehabMeasureId),
                $data['rehabMeasures']
            );
        }

        return Entity::create(
            $case,
            $this->getRepo()->getRefdataReference(Entity::DECISION_DECLARE_UNFIT),
            $data
        );
    }

    /**
     * Retrieves the case entity
     *
     * @return mixed
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getCaseEntity(Cmd $command)
    {
        $case = $this->getRepo()->getReference(
            CasesEntity::class,
            $command->getCase()
        );

        return $case;
    }

    /**
     * Create the task
     *
     * @return CreateTask
     */
    private function createCreateTaskCommand(Cmd $command, CasesEntity $case, TransportManager $transportManager)
    {
        $currentUser = $this->getCurrentUser();

        $data = [
            'category' => Category::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => SubCategory::TM_SUB_CATEGORY_DECLARED_UNFIT,
            'description' => 'TM declared unfitness end date ' . $command->getUnfitnessEndDate(),
            'actionDate' => $command->getUnfitnessEndDate(),
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId(),
            'case' => $case->getId(),
            'transportManager' => $transportManager->getId(),
        ];

        return CreateTask::create($data);
    }
}
