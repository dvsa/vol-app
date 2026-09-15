<?php

/**
 * Delete Unlicensed Licence Vehicle
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\LicenceVehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/operator-unlicensed/licence-vehicle")
 * @Transfer\Method("DELETE")
 */
final class DeleteUnlicensedOperatorLicenceVehicle extends AbstractCommand
{
    use Identity;
}
