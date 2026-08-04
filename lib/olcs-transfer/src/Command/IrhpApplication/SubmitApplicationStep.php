<?php

/**
 * SubmitApplicationStep
 *
 * @author Jonathan Thomas
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\PostData;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationStepSlug;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitApplicationOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-application/submit-application-step")
 * @Transfer\Method("PUT")
 */
class SubmitApplicationStep extends AbstractCommand
{
    use Identity;
    use IrhpPermitApplicationOptional;
    use ApplicationStepSlug;
    use PostData;
}
