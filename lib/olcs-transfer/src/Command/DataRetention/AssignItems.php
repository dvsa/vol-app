<?php

namespace Dvsa\Olcs\Transfer\Command\DataRetention;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/data-retention/assign-items")
 * @Transfer\Method("POST")
 */
final class AssignItems extends AbstractCommand
{
    use Ids;
    use User;
}
