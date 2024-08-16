<?php

/**
 * Update DeclareUnfit
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Task\Task as Task;

/**
 * Update DeclareUnfit
 */
final class UpdateDeclareUnfit extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmCaseDecision';

    protected $extraRepos = ['Task'];

    public function handleCommand(CommandInterface $command)
    {
        /** @var Entity $tmCaseDecision */
        $tmCaseDecision = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        /** @var CasesEntity $case */
        $case = $tmCaseDecision->getCase();

        /** @var TransportManager $transportManager */
        $transportManager = $case->getTransportManager();

        if ($tmCaseDecision->getDecision()->getId() !== Entity::DECISION_DECLARE_UNFIT) {
            throw new BadRequestException('Invalid action');
        }

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
        $tmCaseDecision->update($data);

        $this->getRepo()->save($tmCaseDecision);

        $result = new Result();
        $result->addId('tmCaseDecision', $tmCaseDecision->getId());
        $result->addMessage('Decision updated successfully');

        $taskResult = $this->updateTaskCommand($command, $case, $transportManager);
        if ($taskResult) {
            $result->merge($taskResult);
        }

        return $result;
    }

    /**
     * Update the task. We only update the actionDate but we need to query it because tasks are not directly associated
     *
     * @return static
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function updateTaskCommand(CommandInterface $command, CasesEntity $case, TransportManager $transportManager)
    {
        $result = false;

        /** @var Task $task */
        $task = $this->getRepo('Task')->fetchForTmCaseDecision(
            $case,
            $transportManager,
            SubCategory::TM_SUB_CATEGORY_DECLARED_UNFIT
        );

        if (!empty($task) && ($task->getActionDate() !== $command->getUnfitnessEndDate())) {
            $task->setActionDate($command->getUnfitnessEndDate());
            $this->getRepo('Task')->save($task);

            $result = new Result();
            $result->addId('task', $task->getId());
            $result->addMessage('Task action date updated successfully');
        }

        return $result;
    }
}
