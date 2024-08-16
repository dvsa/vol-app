<?php

/**
 * Process Duplicate Vehicle Warning
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Vehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Process Duplicate Vehicle Warning
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessDuplicateVehicleWarning extends AbstractCommand
{
    use Identity;
}
