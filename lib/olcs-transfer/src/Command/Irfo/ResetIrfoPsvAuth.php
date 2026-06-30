<?php

/**
 * Reset IRFO PSV Auth
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irfo/psv-auth/single/reset")
 * @Transfer\Method("PUT")
 */
final class ResetIrfoPsvAuth extends AbstractCommand
{
    use Identity;
}
