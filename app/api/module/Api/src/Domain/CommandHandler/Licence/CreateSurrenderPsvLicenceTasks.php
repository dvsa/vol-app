<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create surrender PSV licence tasks
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateSurrenderPsvLicenceTasks extends AbstractCommandHandler
{
    public function handleCommand(CommandInterface $command)
    {
        $licenceIds = $command->getIds();

        foreach ($licenceIds as $id) {
            $this->createTask($id);
        }

        $this->result->addMessage(count($licenceIds) . ' task(s) created');

        return $this->result;
    }

    protected function createTask($id)
    {
        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'description' => Task::TASK_DESCRIPTION_LICENCE_EXPIRED,
            'actionDate' => (new DateTime('now'))->format('Y-m-d'),
            'licence' => $id
        ];

        $this->handleSideEffect(CreateTask::create($data));
    }
}
