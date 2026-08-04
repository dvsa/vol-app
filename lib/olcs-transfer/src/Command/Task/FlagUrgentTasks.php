<?php

/**
 * FlagUrgentTasks
 */

namespace Dvsa\Olcs\Transfer\Command\Task;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/flag-urgent-tasks")
 * @Transfer\Method("POST")
 */
final class FlagUrgentTasks extends AbstractCommand
{
}
