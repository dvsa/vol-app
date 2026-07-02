<?php

/**
 * Delete IRHP Permit Stock
 */

namespace Dvsa\Olcs\Transfer\Command\Cases\PresidingTc;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/presiding-tc/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
