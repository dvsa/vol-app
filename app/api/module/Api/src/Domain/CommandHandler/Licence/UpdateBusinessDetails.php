<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateBusinessDetails extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Licence';

    /**
     * Handle Command
     *
     * @inheritdoc
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\UpdateBusinessDetails $command DTO
     *
     * @return DomainCmd\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $updateResult = $this->handleSideEffect(
            DomainCmd\Licence\SaveBusinessDetails::create($command->getArrayCopy())
        );
        $this->result->merge($updateResult);

        if (
            ($updateResult->getFlag('tradingNamesChanged') === true)
            && $this->isGranted(Permission::SELFSERVE_USER)
        ) {
            $taskData = [
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_BUSINESS_DETAILS_CHANGE,
                'description' => 'Change to business details',
                'licence' => $command->getId(),
            ];

            $this->result->merge($this->handleSideEffect(DomainCmd\Task\CreateTask::create($taskData)));
        }

        return $this->result;
    }
}
