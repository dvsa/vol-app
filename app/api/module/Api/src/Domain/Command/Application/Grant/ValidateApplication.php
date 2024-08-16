<?php

/**
 * Validate Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Validate Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ValidateApplication extends AbstractCommand
{
    use Identity;
}
