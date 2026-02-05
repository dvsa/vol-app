<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee as CreateFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as Cmd;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Create Application Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateApplicationFee extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['FeeType'];

    public const string DUE_DATE_FORMAT = 'Y-m-d';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $taskId = null;

        if ($this->shouldCreateTask()) {
            $taskResult = $this->handleSideEffect($this->createCreateTaskCommand($command));
            $result->merge($taskResult);

            $taskId = $taskResult->getId('task');
        }

        $result->merge($this->handleSideEffect($this->createCreateFeeCommand($command, $taskId)));

        return $result;
    }

    /**
     * @return CreateTask
     */
    private function createCreateTaskCommand(Cmd $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $currentUser = $this->getCurrentUser();

        $description = 'Application Fee Due';

        if ($command->getDescription() !== null) {
            $description = $command->getDescription();
        }
        $dueDate = new DateTime('now');
        if ($command->getFeeTypeFeeType() === FeeType::FEE_TYPE_GRANT) {
            $dueDate = $dueDate->add(new \DateInterval('P14D'));
        }

        $data = [
            'category' => Task::CATEGORY_APPLICATION,
            'subCategory' => Task::SUBCATEGORY_FEE_DUE,
            'description' => $description,
            'actionDate' => $dueDate->format(self::DUE_DATE_FORMAT),
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId(),
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId()
        ];

        return CreateTask::create($data);
    }

    /**
     * @param $taskId
     * @return CreateFeeCmd
     */
    private function createCreateFeeCommand(Cmd $command, $taskId)
    {
        $feeType = $command->getFeeTypeFeeType() == null ? FeeType::FEE_TYPE_APP : $command->getFeeTypeFeeType();

        $data = [
            'id' => $command->getId(),
            'feeTypeFeeType' => $feeType,
            'task' => $taskId,
            'optional' => $command->getOptional()
        ];

        return CreateFeeCmd::create($data);
    }

    /**
     * Check whether we need to create a task
     *
     * @return boolean
     */
    private function shouldCreateTask()
    {
        return $this->isGranted(Permission::INTERNAL_USER);
    }
}
