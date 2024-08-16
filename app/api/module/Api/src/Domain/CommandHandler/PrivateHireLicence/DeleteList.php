<?php

/**
 * Delete a list of PrivateHireLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PrivateHireLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Delete a list of PrivateHireLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'PrivateHireLicence';
    protected $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $tmeId) {
            /* @var $phl \Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence */
            $phl = $this->getRepo()->fetchById($tmeId);
            $this->getRepo()->delete($phl);
            $result->addMessage("PrivateHireLicence ID {$tmeId} deleted");
            if (
                $this->isGranted(Permission::SELFSERVE_USER) &&
                ($command->getLva() === 'licence')
            ) {
                $data = [
                    'licence' => $command->getLicence(),
                    'category' => CategoryEntity::CATEGORY_APPLICATION,
                    'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_CHANGE_TO_TAXI_PHV_DIGITAL,
                    'description' => 'Taxi licence deleted - ' . $phl->getPrivateHireLicenceNo(),
                    'isClosed' => 0,
                    'urgent' => 0
                ];
                $res = $this->handleSideEffect(CreateTaskCmd::create($data));
                $result->addId('task' . $res->getId('task'), $res->getId('task'));
                $result->addMessage('Task ' . $res->getId('task') . ' created successfully');
            }
        }

        return $result;
    }
}
