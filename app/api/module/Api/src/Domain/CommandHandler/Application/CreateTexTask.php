<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * CreateTexTask
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateTexTask extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application Application */
        $application = $this->getRepo()->fetchUsingId($command);

        // If a 'TEX' task already exists that is linked to the current application, set the status to 'Closed'
        $this->proxyCommand($command, \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class);

        $taskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_TIME_EXPIRED,
            'description' => 'OOO Time Expired',
            'licence' => $application->getLicence()->getId(),
            'application' => $application->getId(),
        ];

        //  if no OOO date then use today
        $oooDate = $application->getOutOfOppositionDate();
        $actionDate = ($oooDate instanceof \DateTime) ? $oooDate : new DateTime();
        $taskData['actionDate'] = $actionDate->format(DateTime::W3C);

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $currentUser = $this->getCurrentUser();
            $taskData['assignedToUser'] = $currentUser->getId();
            $taskData['assignedToTeam'] = $currentUser->getTeam()->getId();
        }

        return $this->handleSideEffect(\Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::create($taskData));
    }
}
