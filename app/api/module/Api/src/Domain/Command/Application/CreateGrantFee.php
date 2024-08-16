<?php

/**
 * Create Grant Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Create Grant Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateGrantFee extends AbstractCommand
{
    use Identity;
}
