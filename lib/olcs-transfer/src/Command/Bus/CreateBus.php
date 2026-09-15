<?php

/**
 * Create Bus
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus")
 * @Transfer\Method("POST")
 */
final class CreateBus extends AbstractCommand
{
    use FieldType\Licence;
}
