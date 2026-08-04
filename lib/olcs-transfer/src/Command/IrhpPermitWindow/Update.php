<?php

/**
 * Update IRHP Permit Window
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitWindow;

use Dvsa\Olcs\Transfer\FieldType\Traits\DaysForPayment;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStock;
use Dvsa\Olcs\Transfer\FieldType\Traits\Iso8601EndDate;
use Dvsa\Olcs\Transfer\FieldType\Traits\Iso8601StartDate;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-permit-window/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use IrhpPermitStock;
    use Iso8601StartDate;
    use Iso8601EndDate;
    use DaysForPayment;
}
