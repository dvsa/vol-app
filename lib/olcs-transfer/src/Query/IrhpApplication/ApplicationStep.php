<?php

/**
 * Application step
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationStepSlug;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitApplicationOptional;

/**
 * @Transfer\RouteName("backend/irhp-application/application-step")
 */
class ApplicationStep extends AbstractQuery
{
    use Identity;
    use IrhpPermitApplicationOptional;
    use ApplicationStepSlug;
}
