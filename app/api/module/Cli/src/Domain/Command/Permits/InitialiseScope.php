<?php

/**
 * Initialise scope
 *
 */

namespace Dvsa\Olcs\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\DeviationOptional;

/**
 * Initialise scope
 */
final class InitialiseScope extends AbstractStockIdCommand
{
    use DeviationOptional;
}
