<?php

/**
 * Create Variation
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/variation")
 * @Transfer\Method("POST")
 */
final class CreateVariation extends AbstractCommand
{
    use FieldType\Identity;
}
