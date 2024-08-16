<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\Category;

/**
 * CloseFeeDueTask
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CloseFeeDueTask extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application Application */
        $application = $this->getRepo()->fetchUsingId($command);

        $tasks = $application->getOpenTasksForCategory(
            Category::CATEGORY_APPLICATION,
            Category::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE
        );

        $taskIdsToClose = [];
        /* @var $task Task */
        foreach ($tasks as $task) {
            $taskIdsToClose[] = $task->getId();
        }

        return $this->handleSideEffect(
            \Dvsa\Olcs\Transfer\Command\Task\CloseTasks::create(['ids' => $taskIdsToClose])
        );
    }
}
