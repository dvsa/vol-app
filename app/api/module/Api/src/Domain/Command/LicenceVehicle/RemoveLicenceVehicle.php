<?php

/**
 * Remove Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\LicenceVehicle;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;

/**
 * Remove Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class RemoveLicenceVehicle extends AbstractCommand
{
    use Licence;
}
