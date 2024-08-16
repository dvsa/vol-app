<?php

/**
 * In Force Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * In Force Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class InForceInterim extends AbstractCommand
{
    use Identity;
}
