<?php

/**
 * Create Vehicle List Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/document/vehicle-list")
 * @Transfer\Method("POST")
 */
final class CreateVehicleListDocument extends AbstractCommand
{
    use Identity;
}
