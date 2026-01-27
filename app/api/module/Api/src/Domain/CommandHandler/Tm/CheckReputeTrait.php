<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Olcs\Logging\Log\Logger;

trait CheckReputeTrait
{
    public const FAIL_TASK_DESC = '%s: repute check unavailable';

    private function createFailureTask(int $transportManagerId, string $tmName): CreateTask
    {
        $data = [
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK,
            'description' => sprintf(self::FAIL_TASK_DESC, $tmName),
            'actionDate' => date('Y-m-d'),
            'transportManager' => $transportManagerId,
        ];

        return CreateTask::create($data);
    }

    private function logErrorCreateFailureTask(int $transportManagerId, string $tmName, string $errorMsg, $extra = []): void
    {
        Logger::warn($errorMsg, $extra);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createFailureTask($transportManagerId, $tmName)
            )
        );
    }
}
