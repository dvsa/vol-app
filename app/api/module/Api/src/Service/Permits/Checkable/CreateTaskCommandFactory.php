<?php

namespace Dvsa\Olcs\Api\Service\Permits\Checkable;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;

class CreateTaskCommandFactory
{
    /**
     * Create and return a CreateTask command instance using the specified parameters
     *
     *
     * @return CreateTask
     */
    public function create(array $params)
    {
        return CreateTask::create($params);
    }
}
