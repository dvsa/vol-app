<?php

/**
 * Grant People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Grant People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantPeople extends AbstractCommand
{
    use Identity;
}
