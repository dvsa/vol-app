<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Task;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Flag applicable tasks as urgent
 */
class FlagUrgentTasks extends AbstractRawQuery
{
    protected $queryTemplate = 'CALL tasks_flag_urgent';
}
