<?php

/**
 * Approve IRFO GV Permit
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irfo/gv-permit/single/approve")
 * @Transfer\Method("PUT")
 */
final class ApproveIrfoGvPermit extends AbstractCommand
{
    use Identity;
}
