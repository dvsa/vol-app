<?php

/**
 * Delete IRHP Permit Stock
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/irhp-permit-stock/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
