<?php

/**
 * Update period (stock selection)
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * @Transfer\RouteName("backend/irhp-application/single/period")
 * @Transfer\Method("PUT")
 */
class UpdatePeriod extends AbstractCommand
{
    use Traits\Identity;
    use Traits\IrhpPermitStock;
}
#
