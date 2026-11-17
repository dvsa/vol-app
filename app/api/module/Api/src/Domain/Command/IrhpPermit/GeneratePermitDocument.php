<?php

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\Traits\IrhpPermit;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Generate Permit Document for IRHP Permit
 */
final class GeneratePermitDocument extends AbstractCommand
{
    use IrhpPermit;
}
