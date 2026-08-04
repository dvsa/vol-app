<?php

/**
 * Grant IrfoPsvAuth
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irfo/psv-auth/single/grant")
 * @Transfer\Method("PUT")
 */
final class GrantIrfoPsvAuth extends UpdateIrfoPsvAuth
{
}
