<?php

/**
 * Common Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Common Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CommonGrant extends AbstractCommand
{
    use Identity;
}
