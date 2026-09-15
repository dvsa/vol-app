<?php

/**
 * Delete IRHP Permit Range
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitRange;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/irhp-permit-range/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
