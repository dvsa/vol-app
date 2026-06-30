<?php

/**
 * Close Alerts
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\CompaniesHouse;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/companies-house-alert/close")
 * @Transfer\Method("POST")
 */
final class CloseAlerts extends AbstractCommand
{
    use Ids;
}
