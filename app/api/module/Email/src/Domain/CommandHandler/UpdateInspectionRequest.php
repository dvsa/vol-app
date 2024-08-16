<?php

/**
 * Update Inspection Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Email\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Email\Domain\Command\UpdateInspectionRequest as Cmd;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest;

/**
 * Update Inspection Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateInspectionRequest extends AbstractCommandHandler
{
    protected $repoServiceName = 'InspectionRequest';

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        $id = $command->getId();
        $statusCode = $command->getStatus();

        $statuses = [
            'S' => InspectionRequest::RESULT_TYPE_SATISFACTORY,
            'U' => InspectionRequest::RESULT_TYPE_UNSATISFACTORY,
        ];

        if (array_key_exists($statusCode, $statuses)) {

            /** @var InspectionRequest $inspectionRequest */
            $inspectionRequest = $this->getRepo()->fetchById($id);
            $currentStatus = $inspectionRequest->getResultType()->getId();

            if ($statuses[$statusCode] == $currentStatus) {
                // nothing to do
                return $this->result;
            }

            // update inspection request
            $resultType = $this->getRepo()->getRefdataReference($statuses[$statusCode]);
            $inspectionRequest->setResultType($resultType);
            $this->getRepo()->save($inspectionRequest);

            $this->result->merge($this->createTask($inspectionRequest, $resultType));

            return $this->result;
        }

        throw new ValidationException(['ResultType' => 'Result type is invalid']);
    }

    /**
     * Create task using business service
     *
     * @return boolean success
     */
    protected function createTask(InspectionRequest $inspectionRequest, RefData $resultType)
    {
        if ($resultType->getId() === InspectionRequest::RESULT_TYPE_SATISFACTORY) {
            $description = 'Satisfactory inspection request: ID %s';
        } else {
            $description = 'Unsatisfactory inspection request: ID %s';
        }

        $description = sprintf($description, $inspectionRequest->getId());

        $licId = null;
        if ($inspectionRequest->getLicence()) {
            $licId = $inspectionRequest->getLicence()->getId();
        }

        $appId = null;
        if ($inspectionRequest->getApplication()) {
            $appId = $inspectionRequest->getApplication()->getId();
        }

        $taskData = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_INSPECTION_REQUEST_SEMINAR,
            'description' => $description,
            'isClosed' => 'N',
            'urgent' => 'N',
            'licence' => $licId,
            'application' => $appId,
        ];

        return $this->handleSideEffect(CreateTask::create($taskData));
    }
}
