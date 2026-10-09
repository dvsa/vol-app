<?php

/**
 * Approve IrfoPsvAuth
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/irfo/psv-auth/single/approve')]
#[Transfer\Method('PUT')]
final class ApproveIrfoPsvAuth extends UpdateIrfoPsvAuth
{
}
