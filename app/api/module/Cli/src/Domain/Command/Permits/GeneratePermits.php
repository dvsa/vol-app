<?php

/**
 * Generate permits
 */

namespace Dvsa\Olcs\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Generate permits
 */
final class GeneratePermits extends AbstractCommand
{
    use Ids;
    use User;
}
