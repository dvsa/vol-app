<?php

/**
 * Tm Nominated Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Tm Nominated Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class TmNominatedTask extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        $licences = $this->getRepo()->fetchByIds($command->getIds());

        $i = 0;
        /** @var Licence $licence */
        foreach ($licences as $licence) {
            if ($licence->getTmLicences()->isEmpty()) {
                $i++;
                $this->createTask($licence);
            }
        }

        $this->result->addMessage($i . ' tm nominated task(s) created');

        return $this->result;
    }

    protected function createTask(Licence $licence)
    {
        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_TM_PERIOD_OF_GRACE,
            'description' => 'Transport manager to be nominated',
            'actionDate' => (new DateTime('+14 days'))->format('Y-m-d'),
            'assignedToUser' => $this->getUser()->getId(),
            'assignedToTeam' => $this->getUser()->getTeam()->getId(),
            'licence' => $licence->getId()
        ];

        $this->handleSideEffect(CreateTask::create($data));
    }
}
