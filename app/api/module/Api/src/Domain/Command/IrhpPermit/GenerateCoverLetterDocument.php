<?php

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermit;

/**
 * Generate Cover Letter Document for IRHP Permit
 */
final class GenerateCoverLetterDocument extends AbstractCommand
{
    use IrhpPermit;
}
