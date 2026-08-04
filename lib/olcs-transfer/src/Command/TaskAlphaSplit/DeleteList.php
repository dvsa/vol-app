<?php

/**
 * Delete a list of Task Alpha Rule
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\TaskAlphaSplit;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * @Transfer\RouteName("backend/task-alpha-split")
 * @Transfer\Method("DELETE")
 */
class DeleteList extends AbstractCommand
{
    use Ids;
}
