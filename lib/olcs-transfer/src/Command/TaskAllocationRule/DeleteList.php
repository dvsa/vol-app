<?php

/**
 * Delete a list of Task Allocation Rules
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\TaskAllocationRule;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * @Transfer\RouteName("backend/task-allocation-rule")
 * @Transfer\Method("DELETE")
 */
class DeleteList extends AbstractCommand
{
    use Ids;
}
