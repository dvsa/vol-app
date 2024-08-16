<?php

/**
 * Post Submission Tasks
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class PostSubmitTasks extends AbstractCommand
{
    use Identity;
    use IrhpPermitType;
}
