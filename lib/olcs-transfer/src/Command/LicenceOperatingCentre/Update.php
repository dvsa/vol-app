<?php

/**
 * Licence Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\LicenceOperatingCentre;

use Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre\AbstractOperatingCentreCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsTaOverridden;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/licence-operating-centre/single")
 * @Transfer\Method("PUT")
 */
class Update extends AbstractOperatingCentreCommand
{
    use Identity;
    use Version;
    use IsTaOverridden;
}
