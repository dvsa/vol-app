<?php

namespace Dvsa\Olcs\Transfer\Command\TaskAlphaSplit;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/task-alpha-split/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractCommand
{
    use \Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
}
