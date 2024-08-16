<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Service\Permits\Checkable\SubmissionTaskProperties;
use Doctrine\Common\Collections\Criteria;

trait FetchPermitAppSubmissionTaskTrait
{
    /**
     * Return the open task associated with this application that represents the application submission, or null if
     * the task does not exist or has been closed
     *
     * @return Task|null
     */
    public function fetchOpenSubmissionTask()
    {
        $description = $this->getSubmissionTaskDescription();

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('isClosed', 'N'));
        $criteria->andWhere(Criteria::expr()->eq('description', $description));

        $matchingTasks = $this->getTasks()->matching($criteria);

        foreach ($matchingTasks as $task) {
            $categoryMatches = $task->getCategory()->getId() == SubmissionTaskProperties::CATEGORY;
            $subCategoryMatches = $task->getSubCategory()->getId() == SubmissionTaskProperties::SUBCATEGORY;

            if ($categoryMatches && $subCategoryMatches) {
                return $task;
            }
        }

        return null;
    }
}
