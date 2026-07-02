<?php

/**
 * Generate IrfoPsvAuth
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irfo/psv-auth/single/generate")
 * @Transfer\Method("PUT")
 */
final class GenerateIrfoPsvAuth extends UpdateIrfoPsvAuth
{
}
