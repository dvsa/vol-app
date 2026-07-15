<?php

/**
 * Delete Psv Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/named-single/psv-vehicles")
 * @Transfer\Method("DELETE")
 */
final class DeletePsvVehicle extends AbstractCommand
{
    use Application;
    use Ids;
}
