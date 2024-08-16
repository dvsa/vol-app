<?php

namespace Dvsa\Olcs\Api\Service\Permits\Checkable;

use Dvsa\Olcs\Api\Entity\Task\Task;

class SubmissionTaskProperties
{
    public const CATEGORY = Task::CATEGORY_PERMITS;
    public const SUBCATEGORY = Task::SUBCATEGORY_APPLICATION;
}
