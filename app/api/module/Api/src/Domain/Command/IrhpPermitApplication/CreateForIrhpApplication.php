<?php

/**
 * Create Irhp Permit Application for IRHP Applicaton/Window
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplication;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitWindow;

final class CreateForIrhpApplication extends AbstractCommand
{
    use IrhpApplication;
    use IrhpPermitWindow;
}
