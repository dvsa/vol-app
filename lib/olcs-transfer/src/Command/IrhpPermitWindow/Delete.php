<?php

/**
 * Delete IRHP Permit Stock
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitWindow;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/irhp-permit-window/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
