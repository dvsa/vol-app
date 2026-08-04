<?php

/**
 * Delete IRHP Permit Stock
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitApplication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/irhp-permit-application/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
