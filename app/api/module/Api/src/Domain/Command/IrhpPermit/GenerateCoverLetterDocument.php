<?php

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\Traits\IrhpPermit;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Generate Cover Letter Document for IRHP Permit
 */
final class GenerateCoverLetterDocument extends AbstractCommand
{
    use IrhpPermit;
}
