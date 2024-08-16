<?php

/**
 * Grant Condition Undertaking
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Grant Condition Undertaking
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantConditionUndertaking extends AbstractCommand
{
    use Identity;
}
