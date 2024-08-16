<?php

namespace Dvsa\Olcs\Api\Domain\Command\Vehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Remove Duplicate Vehicle
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class RemoveDuplicateVehicle extends AbstractCommand
{
    use Identity;
}
