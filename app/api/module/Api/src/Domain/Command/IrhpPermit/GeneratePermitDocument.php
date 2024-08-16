<?php

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermit;

/**
 * Generate Permit Document for IRHP Permit
 */
final class GeneratePermitDocument extends AbstractCommand
{
    use IrhpPermit;
}
