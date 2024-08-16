<?php

/**
 * Expire IRHP Application
 */

namespace Dvsa\Olcs\Api\Domain\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class Expire extends AbstractCommand
{
    use Identity;
}
