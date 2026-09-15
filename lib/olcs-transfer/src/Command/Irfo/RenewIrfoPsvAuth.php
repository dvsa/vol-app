<?php

/**
 * Renew IRFO PSV Auth
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * @Transfer\RouteName("backend/irfo/psv-auth/renew")
 * @Transfer\Method("PUT")
 */
final class RenewIrfoPsvAuth extends AbstractCommand
{
    use Ids;
}
