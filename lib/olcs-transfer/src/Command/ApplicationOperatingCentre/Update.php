<?php

/**
 * Application Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsTaOverridden;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application-operating-centre/single")
 * @Transfer\Method("PUT")
 */
class Update extends AbstractOperatingCentreCommand
{
    use Identity;
    use Version;
    use IsTaOverridden;
}
