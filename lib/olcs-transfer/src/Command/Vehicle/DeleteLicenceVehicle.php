<?php

/**
 * Delete Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Vehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/licence-vehicle")
 * @Transfer\Method("DELETE")
 */
final class DeleteLicenceVehicle extends AbstractCommand
{
    use Ids;
}
