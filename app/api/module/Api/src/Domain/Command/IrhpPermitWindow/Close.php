<?php

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermitWindow;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Close IRHP Permit Window
 */
final class Close extends AbstractCommand
{
    use Identity;
}
