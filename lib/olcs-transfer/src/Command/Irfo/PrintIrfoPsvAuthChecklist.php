<?php

/**
 * Print IRFO PSV Auth Checklist
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * @Transfer\RouteName("backend/irfo/psv-auth/checklist/print")
 * @Transfer\Method("PUT")
 */
final class PrintIrfoPsvAuthChecklist extends AbstractCommand
{
    use Ids;
}
