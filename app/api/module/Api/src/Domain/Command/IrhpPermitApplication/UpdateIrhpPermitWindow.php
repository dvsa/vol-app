<?php

/**
 * Update Irhp Permit Application Window
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitWindow;

final class UpdateIrhpPermitWindow extends AbstractIdOnlyCommand
{
    use IrhpPermitWindow;
}
