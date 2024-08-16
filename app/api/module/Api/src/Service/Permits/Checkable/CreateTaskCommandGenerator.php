<?php

namespace Dvsa\Olcs\Api\Service\Permits\Checkable;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;

class CreateTaskCommandGenerator
{
    /**
     * Create service instance
     *
     *
     * @return CreateTaskCommandGenerator
     */
    public function __construct(private readonly CreateTaskCommandFactory $createTaskCommandFactory)
    {
    }

    /**
     * Get a CreateTask command instance representing the application submission task for the provided application
     *
     *
     * @return CreateTask
     */
    public function generate(CheckableApplicationInterface $application)
    {
        $params = [
            'category' => SubmissionTaskProperties::CATEGORY,
            'subCategory' => SubmissionTaskProperties::SUBCATEGORY,
            'description' => $application->getSubmissionTaskDescription(),
            $application->getCamelCaseEntityName() => $application->getId(),
            'licence' => $application->getLicence()->getId()
        ];

        return $this->createTaskCommandFactory->create($params);
    }
}
