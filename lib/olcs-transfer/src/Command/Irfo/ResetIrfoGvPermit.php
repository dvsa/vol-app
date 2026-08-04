<?php

/**
 * Reset IRFO GV Permit
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irfo/gv-permit/single/reset")
 * @Transfer\Method("PUT")
 */
final class ResetIrfoGvPermit extends AbstractCommand
{
    use Identity;
}
