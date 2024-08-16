<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Clear as ClearSurrender;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as UpdateSurrender;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks;
use Dvsa\Olcs\Transfer\Query\Surrender\PreviousLicenceStatus;

class Withdraw extends AbstractSurrenderCommandHandler
{
    use AuthAwareTrait;

    protected $extraRepos = ['Licence', 'Task'];

    protected $licenceId;

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->licenceId = $command->getId();

        $this->handleSurrender();
        $this->handleTasks();
        $this->handleLicence();

        $this->result->addMessage('Withdrawn surrender for licence ' . $this->licenceId);

        return $this->result;
    }

    protected function handleSurrender()
    {
        $result = $this->handleSideEffect(UpdateSurrender::create([
            'id' => $this->licenceId,
            'status' => RefData::SURRENDER_STATUS_WITHDRAWN
        ]));
        $this->result->addMessage($result);

        $result = $this->handleSideEffect(ClearSurrender::create([
            'id' => $this->licenceId
        ]));
        $this->result->addMessage($result);
    }

    protected function handleTasks()
    {
        $surrender = $this->getSurrender($this->licenceId);
        $tasks = $this->getRepo('Task')->fetchOpenTasksForSurrender($surrender->getId());

        $taskIds = array_map(fn($task) => $task->getId(), $tasks);

        $result = $this->handleSideEffect(CloseTasks::create([
            'ids' => $taskIds
        ]));
        $this->result->addMessage($result);
    }

    protected function handleLicence()
    {
        /** @var Licence $licence */
        $licence = $this->getRepo('Licence')->fetchById($this->licenceId);

        $this->handleEventHistory($licence, EventHistoryType::EVENT_CODE_SURRENDER_APPLICATION_WITHDRAWN);

        $previousStatus = $this->handleQuery(PreviousLicenceStatus::create(['id' => $this->licenceId]));
        $status = $this->getRepo()->getRefdataReference($previousStatus['status']);
        $licence->setStatus($status);

        $this->getRepo('Licence')->save($licence);
        $this->result->addMessage('Reset status for licence ' . $this->licenceId);
    }
}
